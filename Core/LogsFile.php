<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-24
 * Time: 下午5:03
 */

namespace RdKafkaSdk\Core;


class LogsFile extends Core
{

    /**
     * 日志级别，从上到下，越来越严重
     */
    const DEBUG = 'debug';
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';
    const CRITICAL = 'critical';

    public static function info($service, $message)
    {
        self::write($service, self::INFO, $message);
    }

    public static function warning($service, $message)
    {
        self::write($service, self::WARNING, $message);
    }

    public static function error($service, $message)
    {
        self::write($service, self::ERROR, $message);
    }

    public static function critical($service, $message)
    {
        self::write($service, self::CRITICAL, $message);
    }

    public static function write($service, $level, $message)
    {
        if(is_array($message) || is_object($message)){
            $message = var_export($message, 1);
        } elseif(is_resource($message)){
            $msgType = get_resource_type($message);
            $message = "resource of type ($msgType)";
        }
        self::_localWrite($service, $level, $message);
    }

    private static function _localWrite($service, $level, $message)
    {
        $message = sprintf("%s\t%s\t%s\t%s\n", "[" . date("Y-m-d H:i:s") . "]", $service, $level, $message);
        $file = "/tmp/rdkafka.log";
        return file_put_contents($file, $message, FILE_APPEND);
    }
}