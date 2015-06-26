<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 下午12:48
 */

namespace minions\base;

abstract class SingleInstanceBase {

    protected static $instance;

    protected function __construct(){

    }

    public static function getInstance(){
        if(empty(static::$instance)){
            static::$instance = new static;
        }
        return static::$instance;
    }
}