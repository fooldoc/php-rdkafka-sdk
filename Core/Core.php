<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-18
 * Time: 上午10:06
 */

namespace RdKafkaSdk\Core;


abstract class Core
{
    static private $modules = array();

    /**
     * @param string $val
     * @return $this
     */
    static public function instance($val = '')
    {
        $class = get_called_class();
        if(is_array($val)){
            $keyPrefix = md5(implode('|', $val));
        } else{
            $keyPrefix = $val;
        }
        $key = $val !== '' ? $class . $keyPrefix : $class;
        if(!isset(self::$modules[$key]) || !(self::$modules[$key] instanceof $class)){
            self::$modules[$key] = new $class($val);
        }
        return self::$modules[$key];
    }
}