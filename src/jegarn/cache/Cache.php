<?php

namespace jegarn\cache;
use Exception;
use Redis;

class Cache {
    private static $instance;
    private function __construct(){}
    private function __clone() {}
    /* @var Redis */
    protected $cache;
    protected $config;

    public static function getInstance(){
        if(null === self::$instance) self::$instance = new static();
        $instance = & self::$instance;
        if($instance->config !== null && !$instance->cache){
            ini_set('default_socket_timeout', -1);
            $c = $instance->config;
            $instance->cache= new Redis();
            if(!$instance->cache->connect($c['host'], $c['port'], $c['timeout'])){
                throw new Exception('cache server connect failed');
            }
            if(isset($c['password']) && trim($c['password']) != ""){
                if(!$instance->cache->auth($c['password'])){
                    throw new Exception('cache server auth failed');
                }
            }
        }
        /* @var Redis $instance|Cache $instance */
        return $instance;
    }

    public function initConfig($config){

        $this->config = $config;
    }

    public function preventProcessShare(){
        if($this->cache){
            $this->cache->close();
            $this->cache = null;
        }
    }

    public function __call($name, $arguments){

        return call_user_func_array([$this->cache, $name], $arguments);
    }
}