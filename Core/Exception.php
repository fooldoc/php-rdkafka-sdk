<?php
/**
 * User: www.fooldoc.com
 * Date: 19-12-23
 * Time: 下午3:15
 */

namespace RdKafkaSdk\Core;


class Exception extends \Exception
{
    /**
     * @param string $message
     * @param array  $vars
     */
    public function __construct($message, array $vars = array())
    {
        if($vars){
            array_unshift($vars, $message);
            $message = call_user_func_array('sprintf', $vars);
        }
        list($code, $message) = explode(":", $message, 2);
        parent::__construct($message, $code);
    }

}