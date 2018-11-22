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
        $error= array();
        $nums=$this->db->count_all('orders');
		foreach ($shoplist as $val) {
			$storage = new TokenStorage($val['user_id']);
			$credentials = new Credentials(
				$val['key'],
				$val['secret'],
				getAbsoluteUri()
			);
			$serviceFactory = new ServiceFactory();
			$etsyService = $serviceFactory->createService('Etsy', $credentials, $storage);
			try{
				$shopArr = json_decode($etsyService->request('/shops/' . $val['shop_id'] . '/receipts?includes=Transactions,Listings,Country,Listings:1:0/Images:1:0&limit=1000&was_shipped=false&was_paid=true'), true);
			}catch(Exception $e){
				print $e->getMessage();
				exit();
            };
            // echo json_encode($shopArr);die;
            $insert_data = [];
            if($shopArr['results']){
                foreach ($shopArr['results'] as $value) {
                    foreach ($value['Transactions'] as $key => $order) {
                        $insert_data = [
                            'order_id' => $value['receipt_id'],
                            'creatdTime' => $value['creation_tsz'],//订单创建时间
                            'transaction_id' => $order['transaction_id'],
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
                            'message_from_buyer' => $value['message_from_buyer'].$value['gift_message'],
                            //'message_from_seller' => $value['message_from_seller'],
                            'tracking_code' => '',//$value['shipping_tracking_code'],
                            'carrier_name' => '',
                            'shop_id' => $val['shop_id'],
                            'status' => 1,
                            'country_code' => $value['Country']['iso_country_code'],
                            'price' => $order['price'],
							'total_shipping_cost' => $value['total_shipping_cost'],
                            'formatted_name_a' => isset($order['variations'][0]) ? $order['variations'][0]['formatted_name'] : '',
                            'formatted_value_a' => isset($order['variations'][0]) ? $order['variations'][0]['formatted_value'] : '',
                            'formatted_name_b' => isset($order['variations'][1]) ? $order['variations'][1]['formatted_name'] : '',
                            'formatted_value_b' => isset($order['variations'][1]) ? $order['variations'][1]['formatted_value'] : '',
                            'product_img' => isset($value['Listings'][$key]['Images'][0]['url_170x135']) ? $value['Listings'][$key]['Images'][0]['url_170x135'] : ''
                        ];
                        //$res = $this->db->where('transaction_id', $order['transaction_id'])->get('orders')->row_array();
                        //if (!isset($res['id'])) {
                            //入库
                            //$this->db->insert('orders', $insert_data);
                        //}
                        
                        $insert_query = $this->db->insert_string('orders', $insert_data);
                        $insert_query = str_replace('INSERT INTO','INSERT IGNORE INTO',$insert_query);
                        $this->db->query($insert_query);
                    }
                }

            }else{
                
                array_push($error,$val['name']);
            }
			
            unset($insert_data, $result);
        }

        $nums=$this->db->count_all('orders') - $nums;
   
        $this->apiOut(['code'=>1,'num'=>$nums,'error'=>$error]);

        // redirect('admin/orders');
        // exit();
    }

    //拉取一个店铺订单
    public function pullorderone(){
        $id = $this->uri->segment(4);
        $res = $this->db->where('id',$id)->get('manufacturers')->row_array();
        $storage = new TokenStorage($res['user_id']);
        $credentials = new Credentials(
            $res['key'],
            $res['secret'],
            getAbsoluteUri()
        );
        $serviceFactory = new ServiceFactory();
        $etsyService = $serviceFactory->createService('Etsy', $credentials, $storage);
        try{
            $shopArr = json_decode($etsyService->request('/shops/' . $res['shop_id'] . '/receipts?includes=Transactions,Listings,Country,Listings:1:0/Images:1:0&limit=100&was_shipped=false&was_paid=true'), true);
        }catch(Exception $e){
            print $e->getMessage();
            exit();
        };
        $insert_data = [];
        foreach ($shopArr['results'] as $value) {
            foreach ($value['Transactions'] as $key => $order) {
                $insert_data = [
                    'order_id' => $value['receipt_id'],
                    'creatdTime' => $value['creation_tsz'],//订单创建时间
                    'transaction_id' => $order['transaction_id'],
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
                    'tracking_code' => '',
                    'carrier_name' => '',
                    'shop_id' => $res['shop_id'],
                    'status' => 1,
                    'country_code' => $value['Country']['iso_country_code'],
                    'price' => $order['price'],
					'total_shipping_cost' => $value['total_shipping_cost'],
                    'formatted_name_a' => isset($order['variations'][0]) ? $order['variations'][0]['formatted_name'] : '',
                    'formatted_value_a' => isset($order['variations'][0]) ? $order['variations'][0]['formatted_value'] : '',
                    'formatted_name_b' => isset($order['variations'][1]) ? $order['variations'][1]['formatted_name'] : '',
                    'formatted_value_b' => isset($order['variations'][1]) ? $order['variations'][1]['formatted_value'] : '',
                    'product_img' => isset($value['Listings'][$key]['Images'][0]['url_170x135']) ? $value['Listings'][$key]['Images'][0]['url_170x135'] : ''
                ];
                $res2 = $this->db->where('transaction_id', $order['transaction_id'])->get('orders')->row_array();
                if (!isset($res2['id'])) {
                    //入库
                    $this->db->insert('orders', $insert_data);
                }
            }
        }
        echo '执行成功';
        redirect('admin/orders');
        exit();
    }

    //发货
    public function delivery()
    {
        // $orders = $this->db->where('import', 1)->get('orders')->result_array();
        $array = $_POST['arr'];
        //by Cai Yu
        // $whereArr = [1,4];
        // $orders = $this->db->select('*')->where_in('import', $whereArr)->where('tracking_code !=','')->group_by('order_id')->get('orders')->result_array();
        
        // echo json_encode($orders);die;
        $num=0;
        $ret=0;
        if($array){
            foreach ($array as $val) {
                // echo $val;die;
                $num++;
                $res=$this->delivery1($val);
                // echo $res;die;
                if($res){
                    $ret++;
                }
               
            }
        }
        
        
        // echo json_encode(['code'=>1,'num'=>$num,'res'=>$ret]);die;
        $this->apiOut(['code'=>1,'num'=>$num,'res'=>$ret]);
        
    }

	public function deliveryone(){
        $orderid = $_POST['oid'];
        // echo json_encode($orderid);die;
        $res=$this->delivery1($orderid);
        if($res){
            $this->apiOut(['code'=>1,'num'=>1,'res'=>$res]);
        }else{
            $this->apiOut(['code'=>0,'num'=>1,'res'=>$res]);

        }
        
	}
	
    //发货一个
    public function delivery1($orderid){
        // echo $orderid; die;
		$transferArr = ['wish邮-挂号（上海仓）'=>'USPS', 
			'wish邮-DLE'=>'USPS',
			'wish邮-欧洲标准小包'=>'USPS',
			'wish邮-英伦速邮'=>'USPS',
			'wish邮-wish达'=>'USPS',
			'E邮宝-e邮宝'=>'USPS',
			'出口易-出口易新泽西仓库-新泽西仓库-美国本地经济派送挂号'=>'USPS',
			'出口易-出口易新泽西仓库-新泽西仓库-美国本地标准派送'=>'USPS',
			'出口易-出口易新泽西仓库-新泽西仓库-美国本地邮政派送挂号-Package'=>'USPS',
			'百千诚-美国专线'=>'USPS',
			'百千诚-HKD代理-D'=>'DHL Express',
			'万色物流-Wise美国快线挂号'=>'USPS',
			
			'云途-中美专线(特惠)'=>'USPS',
			'云途-中美专线(标快)'=>'USPS',
            '云途-DHL快递(香港)' => 'DHL Express',
			
			'燕文-韩国epacket小包-深圳'=>'USPS',
			'燕文-燕文航空易派小包-普货'=>'Yanwen',
			'燕文-德邮香港经济小包'=>'Yanwen',
			'燕文-燕文专线追踪小包-普货'=>'Yanwen',
			'燕文-燕文专线追踪小包-特货'=>'Yanwen',
			'燕文-燕文专线平邮小包-普货'=>'Yanwen',
			'燕文-燕文专线平邮小包-特货'=>'Yanwen'
			
			
			];
        
        
		//$orderid = $_POST['oid'];

		$orderinfo = $this->db->where('order_id',$orderid)->get('orders')->row_array();
		$shop_id = $orderinfo['shop_id'];
		$shopinfo = $this->db->where('shop_id', $shop_id)->get('manufacturers')->row_array();
		$storage = new TokenStorage($shopinfo['user_id']);
		$credentials = new Credentials(
			$shopinfo['key'],
			$shopinfo['secret'],
			getAbsoluteUri()
        );
        $res=false;
		$serviceFactory = new ServiceFactory();
		$etsyService = $serviceFactory->createService('Etsy', $credentials, $storage);
		//判断是否发货
        $shopArr = json_decode($etsyService->request('/receipts/'.$orderid), true);
        // echo json_encode($shopArr);die;
		if($shopArr['results'][0]['was_shipped']){
			//已发货
			$updatedata = [
				'order_id' =>$orderid,
                'import' => 2,
                "tracking_code"=>$shopArr['results'][0]['shipping_tracking_code'],
                "carrier_name"=>$shopArr['results'][0]['shipping_carrier'],
                'endTime'=>$shopArr['results'][0]['shipping_notification_date']
			];
			$res = $this->db->update('orders', $updatedata, ['order_id'=>$orderid]);

		}else{
           
            $post = isset($transferArr[trim($orderinfo['carrier_name'])]) ? $transferArr[trim($orderinfo['carrier_name'])] : 'usps';
           

            if($orderinfo['tracking_code']!=''){
                //TODO
                
                $shopArr = json_decode($etsyService->request('/shops/'.$shop_id.'/receipts/' .$orderid.'/tracking', 'post',['tracking_code' => $orderinfo['tracking_code'], 'carrier_name' => $post]), true);
                $res=false;
                if ($shopArr['count'] == 1) {
                    $updatedata = [
                        'order_id' =>$orderid,
                        'import' => 2,
                        'endTime'=> time()
                    ];
                    $res = $this->db->update('orders', $updatedata, ['order_id'=>$orderid]);
                }
            }else{
                $res = false;
            }
			
		}

		if($res){
			return 1;
		}else{
			return 0;
		}

	}

	//取消发货
    public function deleteone(){
        $orderid = $_POST['oid'];
        $type = $_POST['type'];
        $updatedata=['import'=>$type];
        $res = $this->db->update('orders', $updatedata, ['order_id'=>$orderid]);
        if($res){
            echo json_encode(['code'=>0]);
        }else{
            echo json_encode(['code'=>-1]);
        }

    }
    public function deleteones(){
        $array = $_POST['arr'];
        $updatedata=['import'=>3];

        foreach($array as $val){
            $res = $this->db->update('orders', $updatedata, ['order_id'=>$val['order_id']]);
        }
        
       
        echo json_encode(['code'=>0]);
      

    }
}




