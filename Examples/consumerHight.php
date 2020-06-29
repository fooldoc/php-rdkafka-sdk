<?php
/**
 * User: www.fooldoc.com
 * Date: 19-11-4
 * Time: 下午5:28
 */
include_once "../RdKafkaSdk.php";

class consumerHight
{
    /**
     * 高级消费-常用示例
     * note: 高级消费是不支持进程自动中断的,因为初始化的代价比较高,所以不像低级消费一样可以频繁的中止进程
     * 如果需要代码更新必须进行
     * 发送进程的平滑中断的信号:
     * kill -SIGUSR1  PID(进程id)
     * 进程可能会卡住,rdkafka4.0以下的版本会卡住,只要再次强制kill掉就行,问题不大,如果是rdkafka4.0以上的版本则比较少出现这个问题
     * kill -9  PID(进程id)
     */
    public function run()
    {
        //高级消费,是没有支持像低级消费那样可以定时平滑中止的,因为高级消费初始化的代价太大了,不能频繁的中止,
        //如果要中止,自行 kill -
        $callback = function ($message) {
            var_export($message);
            //RdKafka\Message::__set_state(array(
            //   'err' => 0,
            //   'topic_name' => 'test',
            //   'partition' => 0,
            //   'payload' => 'message111',
            //   'len' => 10,
            //   'key' => NULL,
            //   'offset' => 77,
            //))
        };
        $consumerHight = new \RdKafkaSdk\Core\ConsumerHight();
        $brokers = '127.0.0.1:9092';
        $consumerHight->setBrokers($brokers)
            //设置消费组id
            ->setGroupId('test')
            //设置topic名称
            ->setTopic('test_topic')
            ->run($callback);
    }

    /**
     * 高级消费-进阶自定义各种配置
     */
    public function runMulti()
    {
        $callback = function ($message) {
            var_export($message);
            //array (
            //  0 =>
            //  RdKafka\Message::__set_state(array(
            //     'err' => 0,
            //     'topic_name' => 'test',
            //     'partition' => 0,
            //     'payload' => 'message111',
            //     'len' => 10,
            //     'key' => NULL,
            //     'offset' => 81,
            //  )),
            //  1 =>
            //  RdKafka\Message::__set_state(array(
            //     'err' => 0,
            //     'topic_name' => 'test',
            //     'partition' => 0,
            //     'payload' => 'message111',
            //     'len' => 10,
            //     'key' => NULL,
            //     'offset' => 82,
            //  )),
            //)
        };

        $setRebalanceCb = function (\RdKafka\KafkaConsumer $kafka, $err, array $partitions = NULL) {
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
                    echo '--自定义输出callback--' . $message . 'RESP=Assign-' . "\n";
                    $kafka->assign($partitions);
                    break;
                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    echo '--自定义输出callback--' . $message . 'RESP=Revoke' . "\n";
                    $kafka->assign(NULL);
                    break;
                default:
                    echo '--自定义输出callback--' . $message . 'RESP=Error' . "\n";
                    break;
            }
        };
        $consumerHight = new \RdKafkaSdk\Core\ConsumerHight();
        $brokers = '127.0.0.1:9092';
        $consumerHight->setBrokers($brokers)
            //设置消费组id
            ->setGroupId('test')
            //设置topic名称
            ->setTopic('test_topic')
            //同时消费多个topic
            ->setTopic('test_topic_2')
            //设置kafka首次消费位置-从最新的开始消费,底层默认该配置
            ->setOffsetReset(\RdKafkaSdk\Core\Define::OFFSET_RESET_LATEST)
            //自定义配置高级消费的balance回调,底层默认设置好了回调,并记录到了日志Logs文件
            ->setRebalanceCb($setRebalanceCb)
            //设置批量聚合回调数据
            ->setMessageMulti()
            ->run($callback);
    }
}

$m = new consumerHight();
$method = trim($argv[1]);
if(empty($method) || !method_exists($m, $method)){
    $ref = new \ReflectionClass('consumerHight');
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