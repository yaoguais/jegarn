<?php

namespace jegarn\manager;
use Exception;

class BaseManager {

    private static $instance;

    private function __construct(){}
    private function __clone() {}

    /**
     * @param null $class
     *
     * @return static
     * @throws Exception
     */
    public static function getInstance($class = null){

        if($class === null){
            throw new Exception('class name can\'t be null');
        }else{
            if(!isset(self::$instance[$class])){
                self::$instance[$class] = new $class;
            }
        }

        return self::$instance[$class];
    }
}