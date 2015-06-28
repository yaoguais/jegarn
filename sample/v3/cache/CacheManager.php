<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-26
 * Time: 下午8:43
 */

namespace minions\cache;

final class CacheManager{
    private static $_instance;
    private function __construct(){}
    public static function getInstance(){
        return self::$_instance = self::$_instance ? : new self;
    }

    private $_caches;

    public function addCache($key,$config){
        $key = $key ? : ($config['key'] ? : count($this->_caches));
        if(empty($config['class']) || isset($this->_caches[$key]) || !class_exists($config['class'])){
            return false;
        }
        $cache = new $config['class']();
        $cache->init($config);
        $this->_caches[$key] = &$cache;
        return true;
    }

    public function removeCache($key){
        if(null === $key){
            foreach($this->_caches as &$cache){
                $cache->close();
            }
            $this->_caches = [];
        }else if(is_string($key)){
            if(isset($this->_caches[$key])){
                $this->_caches[$key]->close();
                unset($this->_caches[$key]);
            }
        }else if(is_array($key)){
            foreach($key as $k){
                if(isset($this->_caches[$k])){
                    $this->_caches[$k]->close();
                    unset($this->_caches[$k]);
                }
            }
        }else{
            return false;
        }
        return true;
    }
}