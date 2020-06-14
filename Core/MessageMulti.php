<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-24
 * Time: 上午10:23
 */

namespace RdKafkaSdk\Core;


class MessageMulti extends Core
{
    protected $__open = FALSE;
    protected $__num;
    protected $__timeMs;
    protected $__data = [];
    protected $__dataCounter = 0;

    const MULTI_NUM_DEFAULT = 500;
    const MULTI_TIMEMS_DEFAULT = 3000;//毫秒

    public function setOption($multiNum = self::MULTI_NUM_DEFAULT, $multiTimeMs = self::MULTI_TIMEMS_DEFAULT)
    {
        $this->__num = (int)$multiNum;
        $this->__timeMs = (int)$multiTimeMs;
        $this->__open = TRUE;
        return $this;
    }

    public function resetdata()
    {
        $this->__data = [];
        $this->__dataCounter = 0;
        return $this;
    }

    public function pushdata($data = [])
    {
        $this->__data[$this->__dataCounter] = $data;
        $this->__dataCounter++;
        return $this;
    }

    public function getData()
    {
        return $this->__data;
    }

    public function getNum()
    {
        return $this->__num;
    }

    public function getTimeMs()
    {
        return $this->__timeMs;
    }

    public function getOpen()
    {
        return $this->__open;
    }

    public function getDataCounter()
    {
        return $this->__dataCounter;
    }
}