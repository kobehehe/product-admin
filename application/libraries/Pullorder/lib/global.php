<?php
ini_set('display_errors',1);            //错误信息
ini_set('display_startup_errors',1);    //php启动错误信息  
error_reporting(-1);                    //打印出所有的 错误信息  
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 
// 配置PHP运行环境 wangchognwen
$arr_include_path = explode(PATH_SEPARATOR, ini_get('include_path'));
$arr_new_path[] = '.';
$arr_new_path[] = dirname(__FILE__);
$arr_new_path[] = dirname(dirname(__FILE__));
for ($i = 0; $i < sizeof($arr_include_path); $i++) {
    $str_item = trim($arr_include_path[$i]);
    if ($str_item && '.' != $str_item) $arr_new_path[] = $str_item;
}
ini_set('include_path', implode(PATH_SEPARATOR, $arr_new_path));
define("SITE_DIR", dirname(dirname(__FILE__)));  //
date_default_timezone_set("Asia/Shanghai");


//-------------------------------------------------------------


//程序目录配置

define("WWW_ROOT", SITE_DIR . DIRECTORY_SEPARATOR . "www");
define("VENDOR_DIR", SITE_DIR . DIRECTORY_SEPARATOR . "vendor");

define('LOGS_DIR', SITE_DIR . DIRECTORY_SEPARATOR . "logs");

define('DATA_DIR', SITE_DIR . DIRECTORY_SEPARATOR . "data");

define('OAUTH_LOG', false);

// 业务密钥文件
define('ETSY_KEY', 'q7teqakic2d6sjpwr9c6dsar');
define('ETSY_SECRET', 'wqm3twzbae');


//加载常用库函数
require_once("function.php");