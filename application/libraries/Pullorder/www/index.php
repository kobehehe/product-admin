index.php
<?php
require_once "../lib/global.php";
$url = dirname(getAbsoluteUri())."/php/userlogin.php";

echo $url;
header("location: ".$url);
?>