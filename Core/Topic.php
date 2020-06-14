<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-16
 * Time: 下午6:59
 */

namespace RdKafkaSdk\Core;
class Topic extends Core
{
    protected $__topics = [];
    protected $__topicQueueStart = [];

    public function addTopic($topicName)
    {
        $this->__topics[$topicName] = $topicName;
        return $this;
    }

    public function getTopics()
    {
        return $this->__topics;
    }

    public function addQueueStart($topicName, $partition, $offset)
    {
        $this->__topicQueueStart[$topicName][$partition] = [
            'offset' => $offset,
        ];
        return $this;
    }

    public function getQueueStart()
    {
        return $this->__topicQueueStart;
    }

    /**
     * 0.1-hight专用
     * @param $brokers
     * @return array
     * @throws \RdKafka\Exception
     */
    public function getTopicsHas($brokers)
    {
        if(!$this->__topics){
            return [];
        }
        $result = [];
        $consumer = new \RdKafka\Consumer();
        $consumer->addBrokers($brokers);
        foreach($this->__topics as $topicName){
            $consumer->newTopic($topicName);
        }
        $allInfo = $consumer->getMetadata(0, NULL, 30e2);
        $topics = $allInfo->getTopics();
        foreach($topics as $topic){
            $topicName = $topic->getTopic();
            $partitions = $topic->getPartitions();
            $partitionstmp = [];
            foreach($partitions as $key => $partition){
                $partitionstmp[$key] = [
                    'offset' => 0,
                ];
            }
            $result[$topicName] = [
                'name'       => $topicName,
                'partitions' => $partitionstmp,
            ];
        }
        unset($consumer);
        return $result;
    }

    /**
     * 0.1秒-low专用
     * @param $consumer
     * @param $offset
     * @param $topicConf
     * @return array
     */
    public function getTopicsList($consumer, $offset, $topicConf)
    {
        $result = [];
        if($this->__topicQueueStart){
            foreach($this->__topicQueueStart as $topicName => $item){
                $result[$topicName] = [
                    'name'       => $topicName,
                    'partitions' => $item,
                ];
            }
            return $result;
        }
        if(!$this->__topics){
            return [];
        }
        foreach($this->__topics as $topicName){
            $consumer->newTopic($topicName, $topicConf);
        }
        //第一个参数为true 则取所有的topic展示处理，为false则取newTopicd的所有话题
        $allInfo = $consumer->getMetadata(0, NULL, 30e2);
        $topics = $allInfo->getTopics();
        foreach($topics as $topic){
            $topicName = $topic->getTopic();
            $partitions = $topic->getPartitions();
            $partitionstmp = [];
            foreach($partitions as $key => $partition){
                $partitionstmp[$key] = [
                    'offset' => $offset,
                ];
            }
            $result[$topicName] = [
                'name'       => $topicName,
                'partitions' => $partitionstmp,
            ];
        }
        return $result;
    }
}