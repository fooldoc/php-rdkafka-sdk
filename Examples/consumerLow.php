<?php
/**
 * User: www.fooldoc.com
 * Date: 19-11-4
 * Time: 下午5:28
 */
include_once "../RdKafkaSdk.php";

class consumerLow
{
    /**
     * 低级消费数据-最简单的示例
     */
    public function run()
    {
        //业务只需要关心消费的数据回调即可
        $callback = function ($message) {
            var_export($message);
            //打印示例
            //RdKafka\Message::__set_state(array(
            //   'err' => 0,
            //   'topic_name' => 'test',
            //   'partition' => 0,
            //   'payload' => 'message111',
            //   'len' => 10,
            //   'key' => NULL,
            //   'offset' => 61,
            //))

        };
        $brokers = '127.0.0.1:9092';
        $consumerLow = new \RdKafkaSdk\Core\ConsumerLow();
        //设置brokers-支持数组或者字符串
        $consumerLow->setBrokers($brokers)
            //设置消费组id
            ->setGroupId('test')
            //设置topic名称
            ->setTopic('test_topic')
            //设置执行58秒后进程自动平滑中止,如果不调用该方法,则为守护进程不会中断
            ->setBreakTimeMs(58000)
            ->run($callback);
    }

    /**
     * 低级消费数据-数据打包批量回调示例
     */
    public function runMessageMulit()
    {
        //由于设置了setMessageMulti,所以message以数组的形式返回多条,这种形式可以满足上游业务需要批量处理数据的场景
        $callback = function ($message) {
            var_export($message);
            //打印示例
            //array (
            //  0 =>
            //  RdKafka\Message::__set_state(array(
            //     'err' => 0,
            //     'topic_name' => 'test',
            //     'partition' => 0,
            //     'payload' => 'message111',
            //     'len' => 10,
            //     'key' => NULL,
            //     'offset' => 62,
            //  )),
            //  1 =>
            //  RdKafka\Message::__set_state(array(
            //     'err' => 0,
            //     'topic_name' => 'test',
            //     'partition' => 0,
            //     'payload' => 'message111',
            //     'len' => 10,
            //     'key' => NULL,
            //     'offset' => 63,
            //  )),
            //)

        };
        $consumerLow = new \RdKafkaSdk\Core\ConsumerLow();
        //设置brokers-支持数组或者字符串
        $brokers = '127.0.0.1:9092';
        $consumerLow->setBrokers($brokers)
            //设置消费组id
            ->setGroupId('test')
            //设置topic名称
            ->setTopic('test_topic')
            //设置执行58000毫秒后进程自动平滑中止,如果不调用该方法,则进程为守护进程不会中断
            ->setBreakTimeMs(58000)
            //设置为聚合数据批量回调-默认参数设置每次聚合500条数据回调一次,或者是当等待时间超过3000毫秒之后不管数据是否达到500条直接进行回调,两个参数都可以定制
            ->setMessageMulti(500, 3000)
            ->run($callback);
    }

    /**
     * 低级消费数据-进阶设置各种配置
     */
    public function runConf()
    {
        //业务只需要关心消费的数据回调即可
        $callback = function ($message) {
            var_export($message);
        };
        $consumerLow = new \RdKafkaSdk\Core\ConsumerLow();
        $brokers = '127.0.0.1:9092';
        $consumerLow->setBrokers($brokers)
            //设置消费组id
            ->setGroupId('test')
            //设置topic名称
            ->setTopic('test_topic')
            //支持设置多个topci,同时消费多个topic
            ->setTopic('test_topic_2')
            //设置执行58秒后进程自动平滑中止,如果不调用该方法,则进程为守护进程不会中断
            ->setBreakTimeMs(58000)
            //设置offset的存储形式为 文件类型,不设置该配置则底层默认为 broker形式存储,基本上是不推荐使用file形式的,官网都说了不推荐,并且后续的版本会完全干掉
            ->setConf('offset.store.method', 'file')
            ->setConf('offset.store.path', '/tmp')
            //设置kafka首次消费位置-从最新的开始消费,底层默认该配置
            ->setOffsetReset(\RdKafkaSdk\Core\Define::OFFSET_RESET_LATEST)
            ->run($callback);
    }

    /**
     * 低级消费数据-从某个offset位置进行消费
     */
    public function runOffsetAll()
    {
        //业务只需要关心消费的数据回调即可
        $callback = function ($message) {
            var_export($message);
        };
        $consumerLow = new \RdKafkaSdk\Core\ConsumerLow();
        $brokers = '127.0.0.1:9092';
        $consumerLow->setBrokers($brokers)
            //设置消费组id
            ->setGroupId('test')
            //设置topic名称
            ->setTopic('test_topic')
            //支持设置多个topci,同时消费多个topic
            ->setTopic('test_topic_2')
            //设置消费的所有topic所有分区都从offset位置=10进行开始消费
            ->setOffset(10)
            //设置执行58秒后进程自动平滑中止,如果不调用该方法,则进程为守护进程不会中断
            ->setBreakTimeMs(58000)
            ->run($callback);
    }

    /**
     * 低级消费数据-设置某个topic某个分区从某个offset位置进行消费
     */
    public function runOffsetCustom()
    {
        //业务只需要关心消费的数据回调即可
        $callback = function ($message) {
            var_export($message);
        };
        $consumerLow = new \RdKafkaSdk\Core\ConsumerLow();
        $brokers = '127.0.0.1:9092';
        $consumerLow->setBrokers($brokers)
            //设置消费组id
            ->setGroupId('test')
            //设置topic名称
            ->setTopic('test_topic')
            //支持设置多个topci,同时消费多个topic
            ->setTopic('test_topic_2')
            //设置该topic从分区0,offset=10 开始消费
            ->setTopicQueueStart('test_topic', 0, 10)
            //设置该topic从分区1,offset=100 开始消费
            ->setTopicQueueStart('test_topic_2', 1, 100)
            //设置执行58秒后进程自动平滑中止,如果不调用该方法,则进程为守护进程不会中断
            ->setBreakTimeMs(58000)
            ->run($callback);
    }
}

$m = new consumerLow();
$method = trim($argv[1]);
if(empty($method) || !method_exists($m, $method)){
    $ref = new \ReflectionClass('consumerLow');
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