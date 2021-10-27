<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-24
 * Time: 上午10:23
 */

namespace RdKafkaSdk\Core;


class Producer extends Brokers
{
    /**
     * @var array
     */
    protected $__topics = [];
    /**
     * @var \RdKafka\Producer
     */
    protected $__producer = NULL;


    public function __construct()
    {
        try{
            $this->__modelConf = new Conf();
            $this->__modelTopicConf = new TopicConf();
            $this->__runStartTimeMs();
        } catch(\Exception $exception){
            Logs::instance()->error('__construct-Exception:' . $exception->getMessage());
        }
    }

    public function run($payload, $topicName, $partition = RD_KAFKA_PARTITION_UA)
    {
        try{
            is_null($this->__producer) && $this->__producer();
            $this->__topic($topicName, $partition);
            if(!isset($this->__topics[$topicName])){
                Logs::instance()->error('no find topicname:' . $topicName);
                return $this;
            }
            /**
             * @var $topic \RdKafka\ProducerTopic
             */
            $topic = $this->__topics[$topicName]['topic'];
            $partition = $this->__topics[$topicName]['partition'];
            $topic->produce($partition, 0, $payload, NULL);
            while(($len = $this->__producer->getOutQLen()) > 0){
                /**
                 * 2.Kafka轮询一次就相当于拉取（poll）一定时间段broker中可消费的数据， 在这个指定时间段里拉取，时间到了就立刻返回数据。
                 * 3.例如poll（5000）： 即在5s中内拉去的数据返回到消费者端。
                 */
                $this->__producer->poll(50);
            }
        } catch(\Exception $exception){
            Logs::instance()->error('run-Exception:' . $exception->getMessage());
        }
        return $this;

    }


    protected function __topic($topicName, $partition = RD_KAFKA_PARTITION_UA)
    {
        if(!isset($this->__topics[$topicName])){
            $topicConf = $this->__modelTopicConf->getRdkafkaThis();
            $topic = $this->__producer->newTopic($topicName, $topicConf);
            $this->__topics[$topicName] = [
                'topic' => $topic,
            ];
        }
        //关键逻辑！不初始化topic，覆盖分区！
        $this->__topics[$topicName]['partition'] = $partition;
        return $this;
    }

    protected function __producer()
    {
        $this->__modelTopicConf->defaultInitProducer()->run()->getRdkafkaThis();
        $conf = $this->__modelConf->defaultInit()->defaultInitProducer()->run()->getRdkafkaThis();
        $this->__producer = new \RdKafka\Producer($conf);
        $brokers = $this->getBrokers();
        $this->__producer->addBrokers($brokers);
        return $this;
    }


}