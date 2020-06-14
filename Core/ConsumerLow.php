<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-17
 * Time: 上午10:59
 */

namespace RdKafkaSdk\Core;


class ConsumerLow extends ConsumerBase
{
    /**
     * @var \RdKafka\Consumer
     */
    protected $__consumer;
    /**
     * @var \RdKafka\Queue
     */
    protected $__queue;
    protected $__topicsList = [];

    protected $__offset = RD_KAFKA_OFFSET_STORED;

    protected $__breakTimeMs = FALSE;


    /**
     * 设置kafka进程超时时间，单位毫秒
     * @param int $timeMs
     * @return $this
     */
    public function setBreakTimeMs($timeMs = 55000)
    {
        $this->__breakTimeMs = $timeMs;
        return $this;
    }

    /**
     * 设置offset起跳-［全部topic+全部分区］
     * note:rdkafka扩展自带的该方法并不支持扩展低于4.x版本的，此处向下兼容支持
     * {{{ proto TopicPartition RdKafka\TopicPartition::setOffset($offset)
     * @param $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->__offset = $offset;
        return $this;
    }

    /**
     * 获取offset起跳
     * @return int
     */
    public function getOffset()
    {
        return $this->__offset;
    }

    /**
     * 设置kafka起跳的位置，可以参考：\RdKafkaSdk\Core\Define
     * @param $offsetReset
     * @return $this
     */
    public function setOffsetReset($offsetReset)
    {
        $this->__modelTopicConf->addConfSet('auto.offset.reset', $offsetReset);
        return $this;
    }

    /**
     * 设置offset起跳-［支持定义topic+定义分区］
     * @param     $topicName
     * @param     $partition
     * @param int $offset
     * @return $this
     */
    public function setTopicQueueStart($topicName, $partition, $offset = RD_KAFKA_OFFSET_STORED)
    {
        $this->__modelTopic->addQueueStart($topicName, $partition, $offset);
        return $this;
    }

    /**
     * 低级消费模式的异常捕获是有bug的目前最新的so扩展也没有解决，所以我这边尽可能的捕获，但某些系统级别异常还是会直出无法避免，
     * 尝试过主动收集缓冲区未果．后续可以考虑进程外部收集直出的ＥＲＲＯＲ．
     * @return $this
     * @throws Exception
     */
    protected function __runStart()
    {
        $topicConf = $this->__modelTopicConf->defaultInitLow()->run()->getRdkafkaThis();
        $conf = $this->__modelConf->run()->getRdkafkaThis();
        $this->__consumer = new \RdKafka\Consumer($conf);
        $brokers = $this->getBrokers();
        $this->__consumer->addBrokers($brokers);
        $this->__queue = $this->__consumer->newQueue();
        $topic = $this->__modelTopic;
        $topicList = $topic->getTopicsList($this->__consumer, $this->getOffset(), $topicConf);
        if(!$topicList){
            Logs::instance()->error('no find topicList', TRUE);
            throw new Exception('101:' . 'no find topicList');
        }
        $topics = $topic->getTopics();
        foreach($topics as $topicName => $item){
            if(!isset($topicList[$topicName]['partitions']) || !$topicList[$topicName]['partitions']){
                Logs::instance()->error('no find topicname=' . $topicName, TRUE);
                throw new Exception('102:' . 'no find topicname=' . $topicName);
            }
        }
        foreach($topicList as $topicItem){
            $rk = $this->__consumer->newTopic($topicItem['name'], $topicConf);
            foreach($topicItem['partitions'] as $key => $partition){
                $rk->consumeQueueStart($key, $partition['offset'], $this->__queue);
            }
            $this->__topicsList[$topicItem['name']] = $rk;
        }
        return $this;
    }


    protected function __breakEnd()
    {
        if($this->__breakTimeMs === FALSE){
            return FALSE;
        }
        if(Tools::getTimeMs() - $this->__runStartTimeMs >= $this->__breakTimeMs){
            return TRUE;
        }
        return FALSE;
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
            $break = $this->__breakEnd();
            if($break){
                break;
            }
            $message = $this->__queue->consume($this->__consumeTimesMs);
            if(is_null($message)){
                Logs::instance()->echoLine('No more messages[1]!');
                if($messageMulti->getDataCounter()){
                    $this->__callbackMessageMulti($callback);
                }
                continue;
            }
            $callbackSwitch = function ($message) use ($callback, $messageMulti, $messageMultiNum) {
                $this->__topicsList[$message->topic_name]->offsetStore($message->partition, $message->offset);
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
        if(method_exists('\RdKafka\Consumer', 'flush')){
            $this->__consumer->flush(3000);
        }
    }

    protected function __runEndOne(callable $callback)
    {

        while(TRUE){
            if(Signal::pcntlDispatch()){
                break;
            }
            $break = $this->__breakEnd();
            if($break){
                break;
            }
            $message = $this->__queue->consume($this->__consumeTimesMs);
            if(is_null($message)){
                Logs::instance()->echoLine('No more messages[1]!');
                continue;
            }
            $callbackSwitch = function ($message) use ($callback) {
                $this->__topicsList[$message->topic_name]->offsetStore($message->partition, $message->offset);
                $callback($message);
            };
            $this->__runSwitchMessage($message, $callbackSwitch);
        }
        if(method_exists('\RdKafka\Consumer', 'flush')){
            $this->__consumer->flush(3000);
        }
    }


}