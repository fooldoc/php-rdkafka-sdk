<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-17
 * Time: 下午5:06
 */

namespace RdKafkaSdk\Core;


class TopicConf extends ConfBase
{

    /**
     * @var \RdKafka\TopicConf
     */
    protected $__conf;

    public function __construct()
    {
        $this->setRdkafkaThis(new \RdKafka\TopicConf());
    }


    public function defaultInitLow()
    {
        $this->addConfSet('request.required.acks', -1, TRUE);
        $this->addConfSet('auto.commit.interval.ms', 50, TRUE);
        $this->addConfSet('offset.store.method', 'broker', TRUE);
        $this->addConfSet('auto.offset.reset', Define::OFFSET_RESET_LATEST, TRUE);
        return $this;
    }

    public function defaultInitProducer()
    {
        $this->addConfSet('message.timeout.ms', 1050, TRUE);
        return $this;
    }

}