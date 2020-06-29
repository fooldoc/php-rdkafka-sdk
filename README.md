# php-kafka框架
# 项目简介
php-kafka框架，重点关注业务上游，避免重复造轮子

## 功能特性
* 低级消费与高级消费支持数据聚合批量回调，应对上游业务需要聚合数据的，典型的场景：消费到的kafka消息需要一次性聚合查询某个API，而不是消费到一条就查询一次API
* 支持进程自定义时间(毫秒级)来平滑结束进程，并且配合crontab来重新启动，达到平滑更新Php代码，典型的场景：业务大量的使用kafka消费进程，这时候改了某个业务底层代码忘了平滑重启进程，毕竟Php又不像go，常驻进程更新代码很容易遗忘！
* 支持进程常驻，并且支持平滑重启结束进程，发送信号：kill -SIGUSR1  PID(进程id)，既可平滑结束进程
* 避免重复造轮子，简化封装rdkafka常用函数使用，利用callback进行消息回调，重点关注业务上游
* 支持rdkafka3.0以上扩展版本

# 安装使用
1. 这是一个基于php-rdkafka扩展的框架,先安装rdkafka.so扩展,然后即可使用该框架
[php-rdkafka](https://github.com/arnaud-lb/php-rdkafka)

2. 引入入口文件,既可使用框架，基于PSR4 自动加载规范
```php
include_once "../RdKafkaSdk.php";
```
# 代码示例
## 生成数据
### 生产数据-最简单的用法示例
```php
<?php
 //查看执行日志:/tmp/rdkafka.log
        $topic = 'test';
        $brokers = '127.0.0.1:9092';
        $producer = new  \RdKafkaSdk\Core\Producer();
        $producer->setBrokers($brokers)->run('message_test', $topic);
```
### 生产数据-进阶定制用法示例
```php
<?php
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
````
### 生产数据-高阶参数定制用法示例
```php
<?php
        $topic = 'test';
        $producer = new  \RdKafkaSdk\Core\Producer();
        #官方配置文档:https://github.com/edenhill/librdkafka/blob/master/CONFIGURATION.md
        $brokers = '127.0.0.1:9092';
        #修改参数配置-默认底层已经设置了
        $producer->setBrokers($brokers)->setConf('socket.timeout.ms', 50)->setConf('message.timeout.ms', 1050)->run('message111', $topic);
```
## 低级消费
### 低级消费-最简单的示例
```php
<?php
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

```
### 低级消费-数据打包批量回调示例

```php
<?php
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
```
## 高级消费模式示例

### 高级消费-最简单的示例
```php
<?php
        //高级消费,是没有支持像低级消费那样可以定时平滑中止的,因为高级消费初始化的代价太大了,不能频繁的中止,
        //如果要中止,自行 kill -SIGUSR1  PID(进程id)
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

```

### 高级消费-进阶自定义各种配置
```php
<?php
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

```


# 更多示例，直接参考此处可执行文件
[https://github.com/fooldoc/php-rdkafka-sdk/tree/master/Examples](https://github.com/fooldoc/php-rdkafka-sdk/tree/master/Examples)


