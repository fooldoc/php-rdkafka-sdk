<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-24
 * Time: 下午5:03
 */

namespace RdKafkaSdk\Core;


class Logs extends Core
{


    public function echoLine($message)
    {
        echo $message . "\n";
    }

    public function infoProducer($message)
    {
        LogsFile::info('RDKAFKA_PRODUCER', sprintf('setDrMsgCb-success %s', $message));
    }

    public function info($message, $echo = FALSE, $service = '')
    {
        $message = sprintf("base:%s,message:%s", $this->_base(), $message);
        $service = $service ?: 'RDKAFKA';
        LogsFile::info($service, $message);
        $echo && $this->echoLine($message);
    }

    public function error($message, $echo = FALSE, $service = '')
    {
        $message = sprintf("base:%s,message:%s", $this->_base(), $message);
        $service = $service ?: 'RDKAFKA_ERROR';
        LogsFile::warning($service, $message);
        $echo && $this->echoLine($message);
    }

    private function _base()
    {
        $one = debug_backtrace();
        $entry = end($one);
        $file = $entry['file'];
        $function = $entry['function'];
        $class = $entry['class'];
        $message = sprintf('pid=%d,time=%s,file=%s,function=%s,class=%s', RDKAFKASDK_POSIX_GETPID, date('Y-m-d H:i:s'), $file, $function, $class);
        return $message;
    }
}