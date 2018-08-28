<?php

//ini_set("error_reporting","E_ALL & ~E_NOTICE"); 
error_reporting(E_ERROR | E_WARNING | E_PARSE );
//1,即时编译模板。2，有修改就编译
define('APP_DEBUG', 1);
// 定义应用目录
define('APP_PATH','./Application');
include 'JdpPHP/JdpPHP.php';

?>
