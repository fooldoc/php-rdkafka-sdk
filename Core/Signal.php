<?php
/**
 * User: www.fooldoc.com
 * Date: 19-10-25
 * Time: 下午5:14
 */

namespace RdKafkaSdk\Core;


class Signal
{

   static public function RdKafkaSdkSigHandler($signo)
    {
        switch($signo){
            case SIGTERM:
                break;
            case SIGHUP:
                break;
            case SIGUSR1:
                echo "Caught SIGUSR1...\n";
                Logs::instance()->info("\nCaught SIGUSR1 start...", TRUE);
                \RdKafkaSdk\Core\Signal::setSigno(TRUE);
                break;
            default:
        }
    }

    static private $_signo = NULL;

    static public function setSigno($signo)
    {
        self::$_signo = $signo;
    }

    static public function getSigno()
    {
        return self::$_signo;
    }

    static public function pcntlDispatch()
    {
        pcntl_signal_dispatch();
//        Logs::instance()->info("\npcntlDispatch", TRUE);
//        Logs::instance()->info(Signal::getSigno(), TRUE);
        if(Signal::getSigno()){
            Logs::instance()->info("\nCaught SIGUSR1 break", TRUE);
            return TRUE;
        }
        return FALSE;
    }
}