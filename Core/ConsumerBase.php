<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-17
 * Time: 上午11:01
 */

namespace RdKafkaSdk\Core;
abstract class ConsumerBase extends Brokers
{

    protected $__consumeTimesMs = 3000;

    protected $__lockRunTimesMs = 0;

    public function __construct()
    {
        $this->__modelConf = new Conf();
        $this->__modelTopicConf = new TopicConf();
        $this->__modelTopic = new Topic();
    }


    public function run(callable $callback)
    {
        try{
            if(Flock::getPath()){
                $fopen = Flock::fileOpen();
                for($ilock = 1; $ilock <= Flock::getWait(); $ilock++){
                    if(!flock($fopen, LOCK_EX | LOCK_NB)){
                        Logs::instance()->info('已有进程在执行file=' . Flock::getPath(), TRUE);
                        sleep(1);
                    } else{
                        $this->__lockRunTimesMs = $ilock * 1000;
                        break;
                    }
                }
                if($ilock > Flock::getWait()){
                    Logs::instance()->info('进程等待过久,已等待' . Flock::getWait() . 's,直接断开该进程', TRUE);
                    return;
                }
            }
            pcntl_signal(SIGUSR1, "\\RdKafkaSdk\\Core\\Signal::RdKafkaSdkSigHandler");
            $this->__runStartTimeMs()->__runStart();
            $messageMulti = MessageMulti::instance();
            $messageMultiOpen = $messageMulti->getOpen();
            $messageMultiOpen ? $this->__runEndMulti($callback) : $this->__runEndOne($callback);
        } catch(\Exception $exception){
            Logs::instance()->error('run-Exception:' . $exception->getMessage(), TRUE);
        }

    }

    abstract protected function __runStart();

    abstract protected function __runEndMulti(callable $callback);

    abstract protected function __runEndOne(callable $callback);

    /**
     * 进程锁-只支持单机
     * @param $path //文件名
     * @param $second //等待的秒数，超过时间则结束进程等待，防止进程泛滥
     * @return $this
     */
    public function setLock($path, $second)
    {
        Flock::setPath($path);
        Flock::setWait($second);
        return $this;
    }

    /**
     * 支持批量聚合数据一口气返回，自定义聚合数量，还有等待时长
     * 1.注意批量回调的时间肯定会与预设值有偏差！
     * 2.由于回调的业务处理的时差导致！
     * 3.开局很容易1-N !
     * ps:结合自己实际业务来考虑是否使用！
     * @param int $messageMultiNum
     * @param int $messageMultiTimeMs
     * @return $this
     */
    public function setMessageMulti($messageMultiNum = MessageMulti::MULTI_NUM_DEFAULT, $messageMultiTimeMs = MessageMulti::MULTI_TIMEMS_DEFAULT)
    {
        MessageMulti::instance()->setOption($messageMultiNum, $messageMultiTimeMs);
        return $this;
    }

    /**
     * 当消费无数据时，多久心跳一次
     * @param int $timeMs //毫秒
     * @return $this
     */
    public function setConsumeTimesMs($timeMs = 3000)
    {
        $this->__consumeTimesMs = $timeMs;
        return $this;
    }

    /**
     * 消费组id
     * @param $groupId
     * @return $this
     */
    public function setGroupId($groupId)
    {
        $this->__modelConf->addConfSet('group.id', $groupId);
        return $this;
    }

    /**
     * 设置topic,支持多个连贯
     * @param $topicName
     * @return $this
     */
    public function setTopic($topicName)
    {
        $this->__modelTopic->addTopic($topicName);
        return $this;
    }


    /**
     * @param \RdKafka\Message $message
     * @param callable         $callbackSwitch
     */
    protected function __runSwitchMessage($message, callable $callbackSwitch)
    {
        switch($message->err){
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                $callbackSwitch($message);
                break;
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                Logs::instance()->error('No more messages; will wait for more', TRUE);
                break;
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                Logs::instance()->echoLine('No more messages[2]!');
                break;
            default:
                Logs::instance()->error(sprintf('errstr=%s,err=%s', $message->errstr(), $message->err), TRUE);
                break;
        }
    }

    protected function __callbackMessageMulti(callable $callback)
    {
        $callback(MessageMulti::instance()->getData());
        MessageMulti::instance()->resetdata();
    }

    public function __destruct()
    {
        Flock::un();
    }
}