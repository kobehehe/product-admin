<?php





require_once "../../lib/global.php";

debug_request_log();

require_once 'bootstrap.php';
require_once 'Etsy.php';
require_once 'TokenStorage.php';

use OAuth\ServiceFactory;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\TokenStorage;




$session = new Session();

$credentials = new Credentials(
    ETSY_KEY,
    ETSY_SECRET,
    getAbsoluteUri()
);


echo "<hr /> session:"; print_r($session); echo "<br> _SESSION:"; print_r($_SESSION);   echo "<hr />";

//var_dump($serviceFactory); exit('aaa');

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


    echo  "<a target='getshop' href='getshop.php?user_id=$user_id'>查看用戶ID: $user_id 的 shop </a><br />";
    echo '申请结果: <pre>' . print_r($result, true) . '</pre>';

    echo "<hr />\r\n";


    $result = json_decode($etsyService->request('/oauth/scopes'), true);
    echo '验证授权结果: <pre>' . print_r($result, true) . '</pre>';




} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') { //引导浏览器跳转到 Etsy输入用户名和密码
    $response = $etsyService->requestRequestToken();
    $extra = $response->getExtraParams();
    $url = $extra['login_url'];
    header('Location: ' . $url);
    exit();
} else { //提示用户登录
    $url = getAbsoluteUri() . '?go=go';
    echo "<a href='$url'>Login with Etsy!</a>";
}
