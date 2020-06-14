<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-24
 * Time: 上午10:56
 */

namespace RdKafkaSdk\Core;


class Tools
{
    static public function getTimeMs()
    {
        $timeMs = intval(microtime(1) * 1000);
        return $timeMs;

    }
}