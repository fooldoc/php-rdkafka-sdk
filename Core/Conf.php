<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-17
 * Time: 上午11:22
 */

namespace RdKafkaSdk\Core;


class Conf extends ConfBase
{
    /**
     * @var \RdKafka\Conf
     */
    protected $__conf;

    public function __construct()
    {
        $this->setRdkafkaThis(new \RdKafka\Conf());
    }


    public function defaultInit()
    {
        $this->addConfFunction('setStatsCb', function ($consumer, $json) {
            Logs::instance()->error('setStatsCb $json＝' . $json);
        }, TRUE);
        $this->addConfFunction('setErrorCb', function ($kafka, $err, $reason) {
            Logs::instance()->error(sprintf("setErrorCb (error_ori:%s)(error: %s) (reason: %s)", $err, rd_kafka_err2str($err), $reason));
        }, TRUE);
        return $this;
    }

    public function defaultInitProducer()
    {
        $this->addConfFunction('setDrMsgCb', function ($kafka, \RdKafka\Message $message) {
            var_export('111----');
            if($message->err){
                $log = sprintf("%s", var_export($message, 1));
                Logs::instance()->error('setDrMsgCb-error $message＝' . $log);
            } else{
                //                $log = sprintf("%s", var_export($message, 1));
                //                Logs::instance()->echoLine('setDrMsgCb-success $message＝' . $log);
                $str = '';
                foreach($message as $key => $val){
                    $str .= $key . '=' . $val . ';';
                }
                Logs::instance()->infoProducer($str);
            }

        }, TRUE);
        $this->addConfSet('socket.timeout.ms', 50, TRUE);
        if(function_exists('pcntl_sigprocmask')){
            pcntl_sigprocmask(SIG_BLOCK, array(SIGIO));
            $this->addConfSet('internal.termination.signal', SIGIO, TRUE);//设置kafka客户端线程在其完成后立即终止
        } else{
            $this->addConfSet('queue.buffering.max.ms', 1, TRUE);//确保消息尽快发送
        }
        return $this;
    }

    public function defaultInitHight()
    {
        //高级消费模式才有

        $this->addConfFunction('setOffsetCommitCb', function ($consumer, $error, $topicPartitions) {
            if($error !== 0){
                Logs::instance()->error('setOffsetCommitCb error', TRUE);
            }
            /**
             * @var $item \RdKafka\TopicPartition
             * //@todo 后续可以考虑监控系统追加监控
             */
            //            foreach($topicPartitions as $item){
            //                echo "topic=" . $item->getTopic() . ",Partition=" . $item->getPartition() . ",Offset=" . $item->getOffset() . " committed.\n";
            //            }
        }, TRUE);
        $this->addConfSet('queue.buffering.max.ms', 1, TRUE);//单位是毫秒,最大是900000
        $this->addConfSet('request.required.acks', -1, TRUE);//kafka默认值也是-1
        //在interval.ms的时间内自动提交确认、建议不要启动
        //        $topicConf->set('auto.commit.enable', 0); //旧版属性，新版废弃了
        $this->addConfSet('auto.commit.interval.ms', 50, TRUE);
        $this->addConfSet('auto.offset.reset', Define::OFFSET_RESET_LATEST, TRUE);
        return $this;
    }
}