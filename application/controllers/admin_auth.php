<?php

require_once APPPATH . '/libraries/Pullorder/lib/global.php';//"lib/global.php";

debug_request_log();

require_once 'bootstrap.php';
require_once 'Etsy.php';
require_once 'TokenStorage.php';

use OAuth\ServiceFactory;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\TokenStorage;

class Admin_auth extends CI_Controller
{
    //ETSY_KEY,ETSY_SECRET
    public function index()
    {
        $id = $this->uri->segment(4);
        $shopinfo = $this->db->where('id', $id)->get('manufacturers')->row_array();
        //var_dump($shopinfo);die;
        $session = new Session();
        $credentials = new Credentials($shopinfo['key'],//$shopinfo['key']
            $shopinfo['secret'],//$shopinfo['secret']
            getAbsoluteUri()
        );


//        echo "<hr /> session:"; print_r($session); echo "<br> _SESSION:"; print_r($_SESSION);   echo "<hr />";


        $serviceFactory = new ServiceFactory();
        $etsyService = $serviceFactory->createService('Etsy', $credentials, $session);

        if (!empty($_GET['oauth_token'])) { //验证返回
            $token = $session->retrieveAccessToken('Etsy');
            $etsyService->setScopes(array('email_r', 'cart_rw'));
            $token = $etsyService->requestAccessToken(
                $_GET['oauth_token'],
                $_GET['oauth_verifier'],
                $token->getRequestTokenSecret()
            );


            //获取当前登录用户的信息
            $result = json_decode($etsyService->request('/private/users/__SELF__'), true);
            $user_id = $result['results'][0]['user_id'];


            //吧token 序列化保存起来
            $storage = new TokenStorage($user_id);
            $storage->storeAccessToken('Etsy', $token);


            //保存user_id 和 shopid到数据库

            $result = json_decode($etsyService->request('/users/' . $user_id . '/shops'), true);
            $shop_id = $result['results'][0]['shop_id'];

            $data = array(
                'user_id' => $user_id,
                'shop_id' => $shop_id,
            );

            $this->db->where('id', $id);
            $this->db->update('manufacturers', $data);


            echo "<a target='getshop' href='getshop.php?user_id=$user_id'>查看用戶ID: $user_id 的 shop </a><br />";
            echo '申请结果: 授权成功<pre>' . print_r($result, true) . '</pre>';

            echo "<hr />\r\n";


//            $result = json_decode($etsyService->request('/oauth/scopes'), true);
//            echo '验证授权结果: <pre>' . print_r($result, true) . '</pre>';


        } elseif (!empty($_GET['go']) && $_GET['go'] === 'go') { //引导浏览器跳转到 Etsy输入用户名和密码
            $response = $etsyService->requestRequestToken();
            $extra = $response->getExtraParams();
            $url = $extra['login_url'];
            header('Location: ' . $url);
            exit();
        } else { //提示用户登录
            $url = getAbsoluteUri() . '?go=go';
            echo "<a target='_blank' href='$url'>Login with Etsy!</a>";
        }
        exit();
    }

    //拉取订单
    public function pullorder()
    {


        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '0');
        $shoplist = $this->db->get('manufacturers')->result_array();
        $result = [];
        foreach ($shoplist as $val) {
            $storage = new TokenStorage($val['user_id']);
            $credentials = new Credentials(
                $val['key'],
                $val['secret'],
                getAbsoluteUri()
            );
            $serviceFactory = new ServiceFactory();
            $etsyService = $serviceFactory->createService('Etsy', $credentials, $storage);

            $shopArr = json_decode($etsyService->request('/shops/' . $val['shop_id'] . '/receipts?includes=Transactions,Listings,Country,Listings:1:0/Images:1:0&limit=100&was_shipped=false&was_paid=true'), true);
            $insert_data = [];
            foreach ($shopArr['results'] as $value) {
                foreach ($value['Transactions'] as $key => $order) {
                    $insert_data = [
                        'order_id' => $value['receipt_id'],
                        'transaction_id' => $order['transaction_id'],
                        'seller_user_id' => $value['seller_user_id'],
                        'listings_sku' => $order['product_data']['sku'],
                        'listings_title' => $value['Listings'][$key]['title'],
                        'number' => $order['quantity'],
                        'is_gift' => ($value['is_gift']==1)? 1 : $value['needs_gift_wrap'],
                        'subtotal' => $value['subtotal'],
                        'buyer_email' => $value['buyer_email'],
                        'name' => $value['name'],
                        'first_line' => $value['first_line'],
                        'second_line' => $value['second_line'],
                        'city' => $value['city'],
                        'state' => $value['state'],
                        'zip' => $value['zip'],
                        'country' => $value['Country']['name'],
                        'phone' => '',
                        'message_from_buyer' => $value['message_from_buyer'],
                        //'message_from_seller' => $value['message_from_seller'],
                        'tracking_code' => $value['shipping_tracking_code'],
                        'carrier_name' => '',
                        'was_submited' => 0,
                        'shop_id' => $val['shop_id'],
                        'status' => 1,
                        'country_code' => $value['Country']['iso_country_code'],
                        'price' => $order['price'],
						'formatted_name_a' => isset($order['variations'][0]) ? $order['variations'][0]['formatted_name'] : '',
						'formatted_value_a' => isset($order['variations'][0]) ? $order['variations'][0]['formatted_value'] : '',
						'formatted_name_b' => isset($order['variations'][1]) ? $order['variations'][1]['formatted_name'] : '',
						'formatted_value_b' => isset($order['variations'][1]) ? $order['variations'][1]['formatted_value'] : '',
                        'product_img' => isset($value['Listings'][$key]['Images'][0]['url_170x135']) ? $value['Listings'][$key]['Images'][0]['url_170x135'] : ''
                    ];
                    $res = $this->db->where('transaction_id', $order['transaction_id'])->get('orders')->row_array();
                    if (!isset($res['id'])) {
                        //入库
                        $this->db->insert('orders', $insert_data);
                    }
                }
            }
            //$res = $this->db->insert_batch('orders',$insert_data);
            unset($insert_data, $result);
        }
        echo '执行成功';
        redirect('admin/orders');
        exit();
    }


    //发货
    public function delivery()
    {

        $transferArr = ['wish邮-英伦速邮' => 'usps', '云途-DHL快递(香港)' => 'dhl'];

        $orders = $this->db->where('import', 2)->get('orders')->result_array();

        //组建店铺和订单的关系
        $shop2order = [];
        foreach ($orders as $val) {
            $shop2order[$val['shop_id']][$val['order_id']][] = $val;
        }

        //print_r($shop2order);die;
        $updatedata = [];
        foreach ($shop2order as $shop_id => $val) {
            $shopinfo = $this->db->where('shop_id', $shop_id)->get('manufacturers')->row_array();
            $storage = new TokenStorage($shopinfo['user_id']);
            $credentials = new Credentials(
                $shopinfo['key'],
                $shopinfo['secret'],
                getAbsoluteUri()
            );
            $serviceFactory = new ServiceFactory();
            $etsyService = $serviceFactory->createService('Etsy', $credentials, $storage);
            foreach ($val as $key=>$value) {
                $post = isset($transferArr[trim($value[0]['Logistics_mode'])]) ? $transferArr[trim($value[0]['Logistics_mode'])] : 'usps';
                $shopArr = json_decode($etsyService->request('/shops/'.$shop_id.'/receipts/' .(int)$value[0]['order_id'].'/tracking', 'post',['tracking_code' => $value[0]['Logistics_number'], 'carrier_name' => $post]), true);
                if (isset($shopArr['count'])) {
                    $updatedata[] = [
                        'order_id' => $value[0]['order_id'],
                        'import' => 3
                    ];
                }
                //break;
            }
            //记录成功
            if(!empty($updatedata)){
                $this->db->update_batch('orders', $updatedata, 'order_id');
                $res = $this->db->affected_rows();
				echo '订单号：'.$value[0]['order_id'].'快递号：'.$value[0]['Logistics_number'].'快递公司：'.$post.'发货成功</br>';
            }
			else{
			    echo '订单号：'.$value[0]['order_id'].'快递号：'.$value[0]['Logistics_number'].'快递公司：'.$post.'发货失败！！！！！</br>';
			}

            unset($updatedata);
        }
        if($res !== null){
            echo '执行成功';
        }else{
            echo '执行失败';
        }

    }

    //发货一个
    public function deliveryone(){
		$transferArr = ['wish邮-英伦速邮' => 'usps', '云途-DHL快递(香港)' => 'dhl'];
		$orderid = $_POST['oid'];

		$orderinfo = $this->db->where('order_id',$orderid)->get('orders')->row_array();
		$shop_id = $orderinfo['shop_id'];
		$shopinfo = $this->db->where('shop_id', $shop_id)->get('manufacturers')->row_array();
		$storage = new TokenStorage($shopinfo['user_id']);
		$credentials = new Credentials(
			$shopinfo['key'],
			$shopinfo['secret'],
			getAbsoluteUri()
		);
		$serviceFactory = new ServiceFactory();
		$etsyService = $serviceFactory->createService('Etsy', $credentials, $storage);
		//判断是否发货
		$shopArr = json_decode($etsyService->request('/receipts/'.$orderid), true);
		//print_r($shopArr);die;
		if($shopArr['results'][0]['was_shipped'] == 1){
			//已发货
			$updatedata = [
				'order_id' =>$orderid,
				'import' => 3
			];
			$res = $this->db->update('orders', $updatedata, ['order_id'=>$orderid]);

		}else{
			$post = isset($transferArr[trim($orderinfo['Logistics_mode'])]) ? $transferArr[trim($orderinfo['Logistics_mode'])] : 'usps';
			$shopArr = json_decode($etsyService->request('/shops/'.$shop_id.'/receipts/' .$orderid.'/tracking', 'post',['tracking_code' => $orderinfo['Logistics_number'], 'carrier_name' => $post]), true);
			$res=false;
			if ($shopArr['count'] == 1) {
				$updatedata = [
					'order_id' =>$orderid,
					'import' => 3
				];
				$res = $this->db->update('orders', $updatedata, ['order_id'=>$orderid]);
			}
		}

		if($res){
			echo json_encode(['code'=>0]);
		}else{
			echo json_encode(['code'=>-1]);
		}

	}


}




