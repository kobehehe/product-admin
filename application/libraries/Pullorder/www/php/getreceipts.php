<?php





require_once "../../lib/global.php";

debug_request_log();

require_once 'bootstrap.php';
require_once 'Etsy.php';
require_once 'TokenStorage.php';

use OAuth\ServiceFactory;
//use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\TokenStorage;



$shop_id = $_GET['shop_id'];
$user_id = $_GET['user_id'];  //如果有数据库对应关系，可以根据shop_id获取user_id



//这里用的是 序列号 $user_id 的token,不是session
$storage = new TokenStorage($user_id);

$credentials = new Credentials(
    ETSY_KEY,
    ETSY_SECRET,
    getAbsoluteUri()
);



$serviceFactory = new ServiceFactory();
$etsyService = $serviceFactory->createService('Etsy', $credentials, $storage);

// /shops/:shop_id/receipts
$result = json_decode($etsyService->request('/shops/'.$shop_id.'/receipts'), true);


echo 'receipts list: <pre>' . print_r($result, true) . '</pre>';


echo "<br/> ".print_r($_SESSION, true);