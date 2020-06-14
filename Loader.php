<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-17
 * Time: 上午10:39
 */
function RdKafkaSdkdebug($type, array $params)
{
    $sLine = PHP_SAPI == 'cli' ? "\n------------\n" : "<pre>\n";
    $eLine = PHP_SAPI == 'cli' ? "\n------------\n" : '</pre>';
    foreach($params as $var){
        echo $sLine;
        is_resource($var) ? var_dump($var) : var_export($var);
        echo $eLine;
    }
    if($type == 1){
        exit;
    }
    return TRUE;
}

function __rd()
{
    RdKafkaSdkdebug(2, func_get_args());
}

function __re()
{
    RdKafkaSdkdebug(1, func_get_args());
}

/**
 * 使用PSR4 自动加载规范
 * Class Loader
 */
class Loader
{
    public static function topMap()
    {
        return [
            'RdKafkaSdk' => RDKAFKASDK_ROOT,
        ];
    }


    public static function autoload($class)
    {
        $file = self::parseFile($class);
        if($file && file_exists($file)){
            self::includeFile($file);
        }
    }


    private static function parseFile($class)
    {
        $top = substr($class, 0, strpos($class, '\\'));
        $topMap = self::topMap();
        if(!isset($topMap[$top])){
            return '';
        }
        $topDir = $topMap[$top];
        $filePath = substr($class, strlen($top)) . '.php';
        $path = strtr($topDir . $filePath, '\\', DIRECTORY_SEPARATOR);
        return $path;
    }

    private static function includeFile($file)
    {
        if(is_file($file)){
            include $file;
        }
    }
}
