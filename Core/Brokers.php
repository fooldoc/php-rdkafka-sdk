<?php
/**
 * User: www.fooldoc.com
 * Date: 19-11-4
 * Time: 下午5:28
 */

namespace RdKafkaSdk\Core;


class Brokers
{
    /**
     * @var TopicConf
     */
    protected $__modelTopicConf;
    /**
     * @var Conf
     */
    protected $__modelConf;
    /**
     * @var Topic
     */
    protected $__modelTopic;
    protected $__brokers;
    protected $__runStartTimeMs;

    /**
     * @param $brokers //数组字符串都行
     * @return $this
     */
    public function setBrokers($brokers)
    {
        if(!is_array($brokers) && !is_object($brokers)){
            $brokers = explode(',', $brokers);
        }
        $brokers = implode(',', $brokers);
        $this->__brokers = $brokers;
        return $this;
    }

    public function getBrokers()
    {
        return $this->__brokers;
    }

    public function setTopicConf($name, $value)
    {
        $this->__modelTopicConf->addConfSet($name, $value);
        return $this;
    }

    public function getTopicConf()
    {
        return $this->__modelTopicConf->getConfSet();
    }

    public function getConf()
    {
        return $this->__modelConf->getConfSet();
    }

    public function setConfFunction($function, $callback)
    {
        $this->__modelConf->addConfFunction($function, $callback);
        return $this;
    }

    public function setConfStatsCb($callback)
    {
        $this->__modelConf->addConfFunction('setStatsCb', $callback);
        return $this;
    }

    public function setConfErrorCb($callback)
    {
        $this->__modelConf->addConfFunction('setErrorCb', $callback);
        return $this;
    }

    public function setConf($name, $value)
    {
        $this->__modelConf->addConfSet($name, $value);
        return $this;
    }

    protected function __runStartTimeMs()
    {
        $this->__runStartTimeMs = Tools::getTimeMs();
        return $this;
    }
}