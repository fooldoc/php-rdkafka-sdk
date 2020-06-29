# php-kafka框架

# 项目简介
php-kafka框架，重点关注业务上游，避免重复造轮子

## 功能特性
* 低级消费与高级消费支持数据聚合批量回调，应对上游业务需要聚合数据的，典型的场景：消费到的kafka消息需要一次性聚合查询某个API，而不是消费到一条就查询一次API
* 支持进程自定义时间(毫秒级)来平滑结束进程，并且停止配合crontab来重新启动达到平滑更新生效Php代码的困扰，典型的场景：就是业务大量的使用kafka消费进程，这时候改了某个业务底层代码忘了平滑重启进程，毕竟Php又不像go，常驻进程更新代码很容易遗忘！
* 避免重复造轮子，封装kafka常用配置，利用callback进行消息回调，重点关注业务上游
* 支持rdkafka3.0以上扩展版本

# 安装使用
1. 这是一个基于php-rdkafka扩展的框架,先安装rdkafka.so扩展,然后即可使用该框架
![php-rdkafka](https://github.com/arnaud-lb/php-rdkafka)


# 低级消费模式示例

``` php
<?php
include_once "../RdKafkaSdk.php";
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

``` php

# 高级消费模式示例

``` php
<?php
include_once "../RdKafkaSdk.php";
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

``` php


## Examples

