<?php

namespace jegarn\log;

class Logger{

    const DEBUG     = 100;
    const INFO      = 200;
    const NOTICE    = 250;
    const WARNING   = 300;
    const ERROR     = 400;
    const CRITICAL  = 500;
    const ALERT     = 550;
    const EMERGENCY = 600;

    public static $LEVELS = [
        self::DEBUG     => 'DEBUG',
        self::INFO      => 'INFO',
        self::NOTICE    => 'NOTICE',
        self::WARNING   => 'WARNING',
        self::ERROR     => 'ERROR',
        self::CRITICAL  => 'CRITICAL',
        self::ALERT     => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    ];

    /**
     * @var LoggerHandler[]
     */
    protected static $levelHandlerList;

    public static function addHandler(LoggerHandler $handler){
        self::$levelHandlerList[] = $handler;
    }

    public static function addRecord($level, $message, $context = []){
        if(self::$levelHandlerList){
            foreach(self::$levelHandlerList as $handler){
                $handler->addRecord($level, $message, $context);
            }
        }
        return true;
    }

    public static function addDebug($message, $context = []){
        return self::addRecord(self::DEBUG, $message, $context);
    }

    public static function addInfo($message, $context = []){
        return self::addRecord(self::INFO, $message, $context);
    }

    public static function addNotice($message, $context = []){
        return self::addRecord(self::NOTICE, $message, $context);
    }

    public static function addWarning($message, $context = []){
        return self::addRecord(self::WARNING, $message, $context);
    }

    public static function addError($message, $context = []){
        return self::addRecord(self::ERROR, $message, $context);
    }

    public static function addCritical($message, $context = []){
        return self::addRecord(self::CRITICAL, $message, $context);
    }

    public static function addAlert($message, $context = []){
        return self::addRecord(self::ALERT, $message, $context);
    }

    public static function addEmergency($message, $context = []){
        return self::addRecord(self::EMERGENCY, $message, $context);
    }
}