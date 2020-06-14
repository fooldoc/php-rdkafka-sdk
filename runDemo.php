<?php
/**
 * User: luyihong@4399inc.com
 * Date: 20-6-12
 * Time: 下午4:20
 */
include_once "RdKafkaSdk.php";


class runDemo
{
    /**
     * 生产数据
     */
    public function producer()
    {
        $demo = new \RdKafkaSdk\Entry\Demo();
        $demo->producer();
    }

    /**
     * 生产数据
     */
    public function producer2()
    {
        $demo = new \RdKafkaSdk\Entry\Demo();
        $demo->producer2();
    }

}

$m = new runDemo();
$ref = new \ReflectionClass('runDemo');
$methods = $ref->getMethods();   //返回类中所有方法
echo "-----------------执行demo,选择某个方法:---------------" . PHP_EOL . PHP_EOL;
foreach($methods as $method){
    echo $method->getName() . PHP_EOL;
}
echo PHP_EOL;
$method = trim($argv[1]);
if(empty($method) || !method_exists($m, $method)){
    exit('请选择方法执行!');
}
echo PHP_EOL;
$m->$method();
echo PHP_EOL;