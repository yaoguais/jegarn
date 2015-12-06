<?php

namespace minions\base;
use \Exception;

class Singleton {

    private static $instance;

    private function __construct(){

    }

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