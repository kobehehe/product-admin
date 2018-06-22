<?php


//     /php/getshop.php?user_id=120925109

require_once "../../lib/global.php";

debug_request_log();

require_once 'bootstrap.php';
require_once 'Etsy.php';
require_once 'TokenStorage.php';

use OAuth\ServiceFactory;
//use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\TokenStorage;



$user_id = $_GET['user_id'];





$credentials = new Credentials(
    ETSY_KEY,
    ETSY_SECRET,
    getAbsoluteUri()
);



$storage = new TokenStorage($user_id); //这里用的是 序列号 $user_id 的token,不是session

$serviceFactory = new ServiceFactory();
$etsyService = $serviceFactory->createService('Etsy', $credentials, $storage);


$result = json_decode($etsyService->request('/users/'.$user_id.'/shops'), true);
$shop_id =$result['results'][0]['shop_id'];

//这个地方可以把 $shop_id 和 $user_id 的关系保存到数据库，要查某个shop的数据，找到对应的 $user_id，获取token


//如果保存了shop_id和user_id的关系，这个地方就可以不传user_id了
echo  "<a target='getreceipts' href='getreceipts.php?user_id=$user_id&shop_id=$shop_id'>查看shop ID: $shop_id 的 receipts </a><br />";
echo 'shop list: <pre>' . print_r($result, true) . '</pre>';


echo "<br/> ".print_r($_SESSION, true);