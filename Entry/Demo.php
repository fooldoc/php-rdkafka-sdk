<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-30
 * Time: 下午2:22
 */

namespace RdKafkaSdk\Entry;


class Demo extends Base
{
    /**
     * 生产数据-最简单常用的用法示例
     */
    public function producer()
    {
        //查看执行日志:/tmp/rdkafka.log
        $topic = \RdKafkaSdk\Group\Shanghai::TEST;
        $producerShanghai = $this->producerShanghai();
        $producerShanghai->run('message111', $topic);
    }

    /**
     * 生产数据-进阶定制用法示例
     */
    public function producer2()
    {
        $topic = \RdKafkaSdk\Group\Shanghai::TEST;
        $topic2 = \RdKafkaSdk\Group\Shanghai::TEST2;
        $producerShanghai = $this->producerShanghai();
        $callback = function ($kafka, \RdKafka\Message $message) {
            echo '打印消息:' . "\n";
            var_export($message);
            echo "\n";
            if($message->err){
                //@todo 生产失败的逻辑处理
            } else{
                //@todo 生产成功的逻辑处理
            }
        };
        //设置生产消息失败与成功的回调,默认不调用该方法,底层会有默认记录日志行为\RdKafkaSdk\Core\Conf::defaultInitProducer
        $producerShanghai->setConfDrMsgCb($callback);
        //设置错误回调,如若不设置默认会走该逻辑进行:\RdKafkaSdk\Core\Conf::defaultInit
        $setErrorCbCallback = function ($kafka, $err, $reason) {
            echo sprintf("setErrorCb (error_ori:%s)(error: %s) (reason: %s)", $err, rd_kafka_err2str($err), $reason);
        };
        $producerShanghai->setConfErrorCb($setErrorCbCallback);
        //支持连贯用法多条并且不同的topic!
        $producerShanghai->run('message111', $topic)->run('不同topic写数据', $topic2);
    }


}