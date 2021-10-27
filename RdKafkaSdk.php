<?php
/**
 * User: luyihong@4399inc.com
 * Date: 19-9-17
 * Time: 上午10:14
 */

namespace RdKafkaSdk;
define('RDKAFKASDK_ROOT', dirname(__FILE__));
define('RDKAFKASDK_POSIX_GETPID', 0);//posix_getpid() //函数如果没有被禁用可以用起来
spl_autoload_register(function ($className) {//PSR-4
    $top = substr($className, 0, strpos($className, '\\'));
    $topMap = [
        'RdKafkaSdk' => RDKAFKASDK_ROOT,
    ];
    if(!isset($topMap[$top])){
        return;
    }
    $topDir = $topMap[$top];
    $filePath = substr($className, strlen($top)) . '.php';
    $file = strtr($topDir . $filePath, '\\', DIRECTORY_SEPARATOR);

    if($file && file_exists($file)){
        include $file;
    }

});