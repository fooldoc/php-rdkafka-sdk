<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-18
 * Time: 上午10:21
 */

namespace RdKafkaSdk\Core;


abstract class ConfBase extends Core
{
    protected $__confSet = [];
    protected $__conf;
    protected $__confFunction = [];


    public function addConfSet($name, $value, $default = FALSE)
    {
        if($default && isset($this->__confSet[$name])){
            return $this;
        }
        $this->__confSet[$name] = $value;
        return $this;
    }

    public function addConfFunction($function, $callback, $default = FALSE)
    {
        if($default && isset($this->__confFunction[$function])){
            return $this;
        }
        $this->__confFunction[$function] = $callback;
        return $this;
    }

    public function getConfSet()
    {
        return $this->__confSet;
    }

    public function setRdkafkaThis($obj)
    {
        $this->__conf = $obj;
        return $this;
    }

    public function getRdkafkaThis()
    {
        return $this->__conf;
    }

    public function run()
    {
        if($this->__confSet){
            foreach($this->__confSet as $key => $value){
                $this->__conf->set($key, $value);
            }
        }
        if($this->__confFunction){
            foreach($this->__confFunction as $key => $value){
                if(method_exists($this->__conf, $key)){//兼容低版本的rdkafka扩展
                    call_user_func([$this->__conf, $key], $value);
                }

            }
        }
        return $this;
    }

    public function defaultInit()
    {

    }

}