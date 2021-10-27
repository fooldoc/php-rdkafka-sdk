<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-17
 * Time: 上午10:59
 */

namespace RdKafkaSdk\Core;


class ConsumerHight extends ConsumerBase
{


    /**
     * @var \RdKafka\KafkaConsumer
     */
    protected $__kafkaConsumer;

    /**
     * 设置kafka起跳的位置，可以参考：\RdKafkaSdk\Core\Define
     * @param $offsetReset
     * @return $this
     */
    public function setOffsetReset($offsetReset)
    {
        $conf = $this->__modelConf;
        $conf->addConfSet('auto.offset.reset', $offsetReset);
        return $this;
    }


    protected function __runStart()
    {
        /**
         * @var \RdKafka\Conf $conf
         */
        $conf = $this->__modelConf->defaultInit()->defaultInitHight()->run()->getRdkafkaThis();
        $conf->setRebalanceCb(function (\RdKafka\KafkaConsumer $kafka, $err, array $partitions = NULL) {
            $message = '';
            if($partitions){
                $data = [];
                foreach($partitions as $partition){
                    $data[$partition->getTopic()][$partition->getPartition()] = $partition->getOffset();
                }

                foreach($data as $topicName => $val){
                    $message .= sprintf("topic=%s,partitions=%s;", $topicName, implode(',', array_keys($val)));
                }
            }
            switch($err){
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    Logs::instance()->info($message . 'RESP=Assign', TRUE, 'RDKAFKA_HIGHT');
                    $kafka->assign($partitions);
                    break;
                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    Logs::instance()->info($message . 'RESP=Revoke', TRUE, 'RDKAFKA_HIGHT');
                    $kafka->assign(NULL);
                    break;
                default:
                    Logs::instance()->error($message . 'RESP=Error', TRUE, 'RDKAFKA_HIGHT');
                    break;
            }
        });

        $brokers = $this->getBrokers();
        $conf->set('metadata.broker.list', $brokers);
        $this->__kafkaConsumer = new \RdKafka\KafkaConsumer($conf);

        $topics = $this->__modelTopic->getTopics();
        $topicList = $this->__modelTopic->getTopicsHas($brokers);
        foreach($topics as $topicName => $item){
            if(!isset($topicList[$topicName]['partitions']) || !$topicList[$topicName]['partitions']){
                Logs::instance()->error('no find topicname=' . $topicName, TRUE);
                throw new Exception('102:' . 'no find topicname=' . $topicName);
            }
        }
        $this->__kafkaConsumer->subscribe($this->__modelTopic->getTopics());
        return $this;
    }

    protected function __runEndOne(callable $callback)
    {
        while(TRUE){
            if(Signal::pcntlDispatch()){
                break;
            }
            $message = $this->__kafkaConsumer->consume($this->__consumeTimesMs);
            if(is_null($message)){
                Logs::instance()->error('hight message null', TRUE);
                continue;
            }
            $callbackSwitch = function ($message) use ($callback) {
                $callback($message);
            };
            $this->__runSwitchMessage($message, $callbackSwitch);
        }
    }


    protected function __runEndMulti(callable $callback)
    {
        $messageMulti = MessageMulti::instance();
        $messageMulti->resetdata();
        $messageMultiTimeMs = $messageMulti->getTimeMs();
        $messageMultiNum = $messageMulti->getNum();
        $startTimeMs = $this->__runStartTimeMs;
        while(TRUE){
            if(Signal::pcntlDispatch()){
                break;
            }
            $message = $this->__kafkaConsumer->consume($this->__consumeTimesMs);
            if(is_null($message)){
                Logs::instance()->error('hight message null', TRUE);
                if($messageMulti->getDataCounter()){
                    $this->__callbackMessageMulti($callback);
                }
                continue;
            }
            $callbackSwitch = function ($message) use ($callback, $messageMulti, $messageMultiNum) {
                $messageMulti->pushdata($message);
                if($messageMulti->getDataCounter() >= $messageMultiNum){
                    $this->__callbackMessageMulti($callback);
                }
            };
            $this->__runSwitchMessage($message, $callbackSwitch);
            if($messageMulti->getDataCounter() && Tools::getTimeMs() - $startTimeMs >= $messageMultiTimeMs){
                $startTimeMs = Tools::getTimeMs();
                $this->__callbackMessageMulti($callback);
            }
        }
        if($messageMulti->getDataCounter()){
            $this->__callbackMessageMulti($callback);
        }
    }


}