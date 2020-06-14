<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-30
 * Time: 下午2:21
 */

namespace RdKafkaSdk\Entry;

class Base extends \RdKafkaSdk\Core\Core
{


    protected $__lockPath = NULL;
    protected $__lockSecond = 0;


    public function producerBeijing()
    {
        $model = new  \RdKafkaSdk\Core\Producer();
        $model->setBrokers($this->brokersBeijing());
        return $model;
    }


    public function producerShanghai()
    {
        $model = new  \RdKafkaSdk\Core\Producer();
        $model->setBrokers($this->brokersShanghai());
        return $model;
    }

    public function consumerHightBeijing()
    {
        $model = new \RdKafkaSdk\Core\ConsumerHight();
        $model->setBrokers($this->brokersBeijing());
        $this->__runLock($model);
        return $model;
    }

    public function consumerHightShanghai()
    {
        $model = new \RdKafkaSdk\Core\ConsumerHight();
        $model->setBrokers($this->brokersShanghai());
        $this->__runLock($model);
        return $model;
    }

    public function consumerLowBeijing()
    {
        $model = new \RdKafkaSdk\Core\ConsumerLow();
        $model->setBrokers($this->brokersBeijing());
        $this->__runLock($model);
        return $model;
    }

    public function consumerLowShanghai()
    {
        $model = new \RdKafkaSdk\Core\ConsumerLow();
        $model->setBrokers($this->brokersShanghai());
        $this->__runLock($model);
        return $model;
    }

    /**
     * 单机器防止并发锁
     * @param $second
     * @return $this
     */
    public function setLock($second = 10)
    {
        $queue = debug_backtrace();
        $entry = $queue[1]['class'] . '\\' . $queue[1]['function'];
        $file = str_replace('\\', '_', $entry) . '.lock';
        $this->__lockPath = $file;
        $this->__lockSecond = $second;
        return $this;
    }

    /**
     * @param $model \RdKafkaSdk\Core\ConsumerBase
     * @return $this
     */
    protected function __runLock($model)
    {
        if(!$this->__lockPath){
            return $this;
        }
        $model->setLock($this->__lockPath, $this->__lockSecond);
        return $this;
    }

    /**
     * 集群组
     * @return string
     */
    public function brokersShanghai()
    {
        return '172.17.0.1:9092';
    }

    /**
     * 集群组
     * @return string
     */
    public function brokersBeijing()
    {
        return '172.17.0.1:9092';
    }

}