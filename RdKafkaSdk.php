<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-17
 * Time: 上午10:18
 */

namespace RdKafkaSdk;
define('RDKAFKASDK_ROOT', dirname(__FILE__));
define('RDKAFKASDK_POSIX_GETPID', 0);//posix_getpid() //函数如果没有被禁用可以用起来
include 'Loader.php';
spl_autoload_register('Loader::autoload');