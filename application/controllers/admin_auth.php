<?php

require_once APPPATH.'/libraries/Pullorder/lib/global.php';//"lib/global.php";

debug_request_log();

require_once 'bootstrap.php';
require_once 'Etsy.php';
require_once 'TokenStorage.php';

use OAuth\ServiceFactory;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\TokenStorage;

class Admin_auth extends CI_Controller {
        //ETSY_KEY,ETSY_SECRET
    public function index(){
        $id = $this->uri->segment(4);
        $shopinfo = $this->db->where('id',$id)->get('manufacturers')->row_array();
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
            $user_id=$result['results'][0]['user_id'];


            //吧token 序列化保存起来
            $storage = new TokenStorage($user_id);
            $storage->storeAccessToken('Etsy',$token);



            //保存user_id 和 shopid到数据库

            $result = json_decode($etsyService->request('/users/'.$user_id.'/shops'), true);
            $shop_id =$result['results'][0]['shop_id'];

            $data = array(
                'user_id' => $user_id,
                'shop_id' => $shop_id,
            );

            $this->db->where('id',$id);
            $this->db->update('manufacturers', $data);



            echo  "<a target='getshop' href='getshop.php?user_id=$user_id'>查看用戶ID: $user_id 的 shop </a><br />";
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
    public function pullorder(){
        ini_set('memory_limit','1024M');
        ini_set('max_execution_time', '0');
        $shoplist = $this->db->get('manufacturers')->result_array();
        $result=[];
        foreach ($shoplist as $val){
            $storage = new TokenStorage($val['user_id']);
            $credentials = new Credentials(
                $val['key'],
                $val['secret'],
                getAbsoluteUri()
            );
            $serviceFactory = new ServiceFactory();
            $etsyService = $serviceFactory->createService('Etsy', $credentials, $storage);
            $result = json_decode($etsyService->request('/shops/'.$val['shop_id'].'/receipts?limit=100&was_shipped=false&was_paid=true'), true);
            $insert_data=[];
            foreach ($result['results'] as $value){
                $listingArr = json_decode($etsyService->request('/receipts/'.$value['receipt_id'].'/listings'), true);
                $transitionArr = json_decode($etsyService->request('/receipts/'.$value['receipt_id'].'/transactions'), true);
                //var_dump($transition);die;
                //print_r($transitionArr);die;
                foreach ($transitionArr['results'] as $order){

                    //获得图片
                    $imgArr = json_decode($etsyService->request('/listings/'.$listingArr['results'][0]['listing_id'].'/images/'.$order['image_listing_id']), true);
                    $insert_data[]=[
                        'order_id' =>$value['order_id'],
                        'seller_user_id' => $value['seller_user_id'],
                        'listings_sku' => $order['product_data']['sku'],
                        //'listings_title' => $listingArr['results'][0]['title'],
                        'number'=>$order['quantity'],
                        'is_gift'=> $value['is_gift'],
                        'subtotal' => $value['subtotal'],
                        'buyer_email' => $value['buyer_email'],
                        'name' => $value['name'],
                        'first_line' => $value['first_line'],
                        'second_line' => $value['second_line'],
                        'city' => $value['city'],
                        'state' => $value['state'],
                        'zip' => $value['zip'],
                        'country' => $value['country_id'],
                        'phone' => '',
                        'message_from_buyer'=>$value['message_from_buyer'],
                        'message_from_seller'=>$value['message_from_seller'],
                        'tracking_code' => $value['shipping_tracking_code'],
                        'carrier_name' =>'',
                        'was_submited' =>0,
                        'shop_id'=> $val['shop_id'],
                        'product_img' => $imgArr['results'][0]['url_170x135']
                    ];
                }
            }
            print_r($insert_data);die;
            $res = $this->db->insert_batch('products',$insert_data);
            unset($insert_data,$result);
        }
        $data['main_content'] = 'admin/products/list';
        $this->load->view('includes/template', $data);
    }
}




