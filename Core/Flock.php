<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-18
 * Time: 下午3:12
 */

namespace RdKafkaSdk\Core;


class Flock
{
    const FLOCK_PATH = '/tmp/%s';

    static private $_path = NULL;

    static protected $_fopen = NULL;

    static private $_second = 0;

    static public function setPath($fileName)
    {
        self::$_path = sprintf(self::FLOCK_PATH, $fileName);
    }

    static public function setWait($second)
    {
        self::$_second = $second;
    }

    static public function getWait()
    {
        return self::$_second;
    }

    static public function getPath()
    {
        return self::$_path;
    }


    static public function fileOpen()
    {
        $path = self::getPath();
        if(!is_file($path)){
            $fopen = fopen($path, 'a');
            chmod($path, 0666);
        } else{
            $fopen = fopen($path, 'a');
        }
        if($fopen === FALSE){//没有权限
            Logs::instance()->error('flock-没有写入权限!file=' . $path, TRUE);
            exit;
        }
        self::$_fopen = $fopen;
        return $fopen;
    }

    static public function un()
    {
        if(self::$_fopen){
            flock(self::$_fopen, LOCK_UN);
            fclose(self::$_fopen);
        }
    }

}