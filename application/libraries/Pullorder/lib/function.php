<?php


function mkdirp($working_directory)
{
    do {
        $dir = $working_directory;
        if (is_dir($dir)) {
            return true;
        }
        while (!is_dir(dirname($dir))) {
            $dir = dirname($dir);
        }
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777)) {
                //script_log('创建目录 $dir 失败。', SHELL_ELVL_ERR, 20003014);
                return;
                //throw new Exception("创建目录 $dir 失败。");
            }
        }
    } while ($dir != $working_directory);
}


function unlinkf($file)
{
    if (!file_exists($file)) {
        return true;
    }
    if (@unlink($file)) {
        return true;
    }
    /*
    $cmd = 'del "'.str_replace('/', "\\", $file).'"';
    exec($cmd );
    if(!file_exists($file)) {
        return true;
    }
    */
    /*
    $fso = new COM("Scripting.FileSystemObject");
    $MyFile = $fso->GetFile($file);
    $MyFile->attributes = 0;
    unset($MyFile);
    unset($fso);
    if(@unlink($file)) {
        return true;
    }
    */
    return false;

}


function http_request($method, $url, $postfields = null, $connect_timeout = 0, $timeout = 180, $otherheader = array())
{
    $arr_return = array();
    $arr_return['httpcode'] = 0;
    $arr_return['errno'] = 0;
    $arr_return['error'] = '';
    $arr_return['headers'] = array();
    $arr_return['content'] = '';
    $arr_return['curl'] = array();


    $ch = curl_init();
    if (is_null($timeout)) $timeout = 180;
    $method = strtoupper($method);
    if ($method == "POST") {
        curl_setopt($ch, CURLOPT_POST, true);
    } else {
        //curl_setopt($ch, CURLOPT_POST, false);
    }

    if ($method == "PUT") {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT'));

    }
    if (stripos($url, 'https:') === 0) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    if ($method == "DELETE") {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: DELETE'));

    }

    //postfields 会自动转成POST
    if (!empty($postfields)) {

        if ($method == "POST" or $method == "PUT" or $method == "DELETE") {
            if (is_array($postfields)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                //die(http_build_query($postfields));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
            }
        } else {
            if (is_array($postfields)) {
                if (strpos($url, '?') == false) {
                    $url = $url . '?' . http_build_query($postfields);
                } else {
                    $url = $url . '&' . http_build_query($postfields);
                }
                //die(http_build_query($postfields));
            } else {
                if (strpos($url, '?') == false) {
                    $url = $url . '?' . $postfields;
                } else {
                    $url = $url . '&' . $postfields;
                }

            }
        }
    }


    curl_setopt($ch, CURLOPT_URL, $url);
    if ($connect_timeout > 0) curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
    if ($timeout > 0) curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

    if (!empty($otherheader)) {
        if (is_array($otherheader)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $otherheader);
        }
    }


    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_HTTPHEADER, $arr_headers);
    //跟踪重定向
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    //最多跟踪重定向5次
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    //支持所有压缩格式
    curl_setopt($ch, CURLOPT_ENCODING, "");


    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $arr_return['curl'] = curl_getinfo($ch);
    $curl_errno = curl_errno($ch);

    if ($curl_errno != 0) {
        $curl_error = curl_error($ch);
    } else {
        $curl_error = '';
        //$curl_error = curl_error($ch);
    }

    curl_close($ch);


    $arr_return['httpcode'] = $httpcode;
    $arr_return['errno'] = $curl_errno;
    $arr_return['error'] = $curl_error;


    if ($curl_errno == 0) {
        $str_header = "";
        $content = $response;
        while (preg_match("/^HTTP\/[01]\.[0-9xX]\s[1-5][0-9]{2}/", $content, $pattern)) {
            //print_r($pattern);
            $arr_response = explode("\r\n\r\n", $content, 2);
            $str_header = $arr_response[0];
            $content = $arr_response[1];
        }
        $arr_return['content'] = $content;
        $arr_headers = explode("\r\n", $str_header);
        $int_cnt_header = sizeof($arr_headers);
        for ($i = 0; $i < $int_cnt_header; $i++) {

            $str_entry = trim($arr_headers[$i]);
            if (strpos($str_entry, ":") === false) {
            } else {
                $arr_entry = explode(":", $str_entry, 2);
                $arr_return['headers'][$arr_entry[0]] = trim($arr_entry[1]);
            }

        }
    }
    //print_r($arr_return);
    return $arr_return;

}



function getCookie($key) {
    if(!isset($_COOKIE[$key])) return null;
    return $_COOKIE[$key];
}

function getPost($key) {
    if(!isset($_POST[$key])) return null;

    if(is_array($_POST[$key])) {
        if (get_magic_quotes_gpc()) {
            return array_map('stripslashes_deep', $_POST[$key]);
        } else {
            return $_POST[$key];
        }
    } else {
        return get_magic_quotes_gpc() ? stripslashes($_POST[$key]) : $_POST[$key];
    }

}

function getRequest($key) {
    if(isset($_POST[$key])){
        if(is_array($_POST[$key])) {
            if (get_magic_quotes_gpc()) {
                return array_map('stripslashes_deep', $_POST[$key]);
            } else {
                return $_POST[$key];
            }
        } else {
            return get_magic_quotes_gpc() ? stripslashes($_POST[$key]) : $_POST[$key];
        }

    }
    if(isset($_GET[$key])) {
        if(is_array($_GET[$key])) {
            if (get_magic_quotes_gpc()) {
                return array_map('stripslashes_deep', $_GET[$key]);
            } else {
                return $_GET[$key];
            }
        } else {
            return get_magic_quotes_gpc() ? stripslashes($_GET[$key]) : $_GET[$key];
        }
    }
    return null;
}





function getPut($key = NULL) {
    global $_PUT;
    static $PUT = null;
    if(is_null($PUT)) {
        //echo '获取put ';
        $str_input = file_get_contents('php://input');
        //echo ("\r\nphp://input ---$str_input--- \r\n");
        parse_str($str_input, $PUT);
        $_PUT = $PUT;

    }
    if($key== NULL) return $PUT;
    if(isset($PUT[$key])) return get_magic_quotes_gpc() ? stripslashes($PUT[$key]) : $PUT[$key];
    return null;
}

function stripslashes_deep($value)
{
    $value = is_array($value) ?
        array_map('stripslashes_deep', $value) :
        stripslashes($value);

    return $value;
}



function getBasename($path){
    /**
    $path_parts = pathinfo('/www/htdocs/inc/lib.inc.php');

    echo $path_parts['dirname'], "\n";  /www/htdocs/inc
    echo $path_parts['basename'], "\n";  lib.inc.php
    echo $path_parts['extension'], "\n";  php
    echo $path_parts['filename'], "\n"; //  lib.inc    since PHP 5.2.0
     */
    $path_parts = pathinfo($path);
    return $path_parts['basename'];
}


function getExtension($path){
    $path_parts = pathinfo($path);
    return $path_parts['extension'];
}
function getFilename($path){
    $path_parts = pathinfo($path);
    return $path_parts['filename'];
}


function debug_request_log($logfile="", $type="") {
    if(empty($logfile)) $logfile = LOGS_DIR ."/" . getBasename($_SERVER['PHP_SELF']).".request.log";
    $log_str = date("Y-m-d H:i:s") ." ". $_SERVER['REMOTE_ADDR'] ." ".$_SERVER['HTTP_USER_AGENT']."\r\n";
    $log_str .= $_SERVER["REQUEST_METHOD"]." ". $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"] ."\r\n";
    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $log_str .= http_build_query($_POST) ."\r\n";
    }


    if(!empty($_GET)) {
        $log_str .= "decode get:".   (get_magic_quotes_gpc() ? print_r(array_map('stripslashes_deep', $_GET), true) : print_r($_GET, true) ) ."\r\n";

    }

    if(!empty($_POST)) {
        $log_str .= "decode post:".  (get_magic_quotes_gpc() ? print_r(array_map('stripslashes_deep', $_POST), true) : print_r($_POST, true) ) ."\r\n";
    }

    if($_SERVER["REQUEST_METHOD"] == "PUT") {
        $log_str .= "decode put:".print_r(array_map('stripslashes_deep', getPut()), true) ."\r\n";
    }

    mkdirp(dirname($logfile));
    $handle = fopen($logfile, 'a');
    fwrite($handle, $log_str);
    fclose($handle);

}



function getAbsoluteUri()
{
  $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
  $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
  $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
  $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
  return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
}
