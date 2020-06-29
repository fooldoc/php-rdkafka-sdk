<?php
/**
 * User: www.fooldoc.com
 * Date: 19-11-4
 * Time: 下午5:28
 */
include_once "../RdKafkaSdk.php";

class producer
{

    /**
     * 生产数据-最简单常用的用法示例
     */
    public function run()
    {
        //查看执行日志:/tmp/rdkafka.log
        $topic = 'test';
        $brokers = '127.0.0.1:9092';
        $producer = new  \RdKafkaSdk\Core\Producer();
        $producer->setBrokers($brokers)->run('message_test', $topic);
    }

    /**
     * 生产数据-进阶定制用法示例
     */
    public function runConf()
    {
        $topic = 'test';
        $topic2 = 'test2';
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
        $setErrorCbCallback = function ($kafka, $err, $reason) {
            echo sprintf("setErrorCb (error_ori:%s)(error: %s) (reason: %s)", $err, rd_kafka_err2str($err), $reason);
        };
        $brokers = '127.0.0.1:9092';
        $producer = new  \RdKafkaSdk\Core\Producer();
        //设置brokers-支持数组或者字符串
        $producer->setBrokers($brokers)
            //setConfDrMsgCb--设置生产消息失败与成功的回调,默认底层会调用该事件,如果自定义后就会覆盖底层的事件 \RdKafkaSdk\Core\Conf::defaultInitProducer
            ->setConfDrMsgCb($callback)
            //setConfErrorCb--设置错误回调,如若不设置默认会走该逻辑进行:\RdKafkaSdk\Core\Conf::defaultInit
            ->setConfErrorCb($setErrorCbCallback)
            //支持连贯用法生产多条数据并且是不同的topic
            ->run('message111', $topic)->run('不同topic写数据', $topic2);
    }

    /**
     * 生产数据-高阶参数定制用法示例
     */
    public function runConfMulti()
    {
        $topic = \RdKafkaSdk\Topic\Shanghai::TEST;
        $producer = new  \RdKafkaSdk\Core\Producer();
        #官方配置文档:https://github.com/edenhill/librdkafka/blob/master/CONFIGURATION.md
        $brokers = '127.0.0.1:9092';
        #修改参数配置-默认底层已经设置了
        $producer->setBrokers($brokers)->setConf('socket.timeout.ms', 50)->setConf('message.timeout.ms', 1050)->run('message111', $topic);
    }
}

$m = new producer();
$method = trim($argv[1]);
if(empty($method) || !method_exists($m, $method)){
    $ref = new \ReflectionClass('producer');
    $methods = $ref->getMethods();   //返回类中所有方法
    echo "-----------------执行demo,选择某个方法:---------------" . PHP_EOL . PHP_EOL;
    foreach($methods as $method){
        echo $method->getName() . PHP_EOL;
    }
    echo PHP_EOL;
    exit('请选择方法执行!');
}
echo PHP_EOL;
$m->$method();
echo PHP_EOL;