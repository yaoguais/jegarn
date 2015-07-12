<?php

namespace jegern\cache;

class RedisCache implements ICache{

    protected $cache;
    protected $config;
    protected $dbName;

    public function init($config) {
        $this->config = $config;
    }

    public function open() {
        if(empty($this->cache)){
            if(!extension_loaded('redis')){
                return false;
            }
            $this->cache = new \Redis();
            $func = isset($this->config['persistent']) && $this->config['persistent'] ? 'pconnect' : 'connect';
            $host = isset($this->config['host']) ? $this->config['host'] : '127.0.0.1';
            $port = isset($this->config['port']) ? $this->config['port'] : 6379;
            $timeout = isset($this->config['timeout']) ? $this->config['timeout'] : 0;
            return $this->cache->{$func}($host,$port,$timeout);
        }
        return true;
    }

    public function close() {
        if($this->cache){
            $this->cache->close();
            unset($this->cache);
            $this->cache = $this->config = $this->dbName = null;
        }
    }

    public function increase($key, $step = 1) {
        if(!$this->open()){
            return false;
        }
        return $this->cache->incrBy($key,$step);
    }

    public function decrease($key, $step = 1) {
        if(!$this->open()){
            return false;
        }
        return $this->cache->decrBy($key,$step);
    }

    public function useDb($dbName) {
        if(!$this->open()){
            return false;
        }
        if($this->dbName != $dbName){
            $this->dbName = $dbName;
            return $this->cache->select($dbName);
        }
        return true;
    }

    public function useTable($tableName) {
        return true;
    }

    public function set($key, $value) {
        if(!$this->open()){
            return false;
        }
        return $this->cache->set($key,$value);
    }

    public function get($key) {
        if(!$this->open()){
            return false;
        }
        return $this->cache->get($key);
    }

    public function delete($key){
        if(!$this->open()){
            return false;
        }
        return $this->cache->delete($key);
    }

    public function setMap($key, $map) {
        if(!$this->open()){
            return false;
        }
        return $this->cache->hMset($key,$map);
    }

    public function getMap($key, $fields=null) {
        if(!$this->open()){
            return false;
        }
        if(empty($fields)){
            return $this->cache->hGetAll($key);
        }
        return $this->cache->hMget($key,$fields);
    }

    public function deleteMap($key){
        return $this->delete($key);
    }

    public function addToSet($key, $value) {
        if(!$this->open()){
            return false;
        }
        return $this->cache->sAdd($key,$value);
    }

    public function removeFromSet($key, $value) {
        if(!$this->open()){
            return false;
        }
        return $this->cache->sRemove($key,$value);
    }

    public function deleteSet($key){
        return $this->delete($key);
    }

    public function getSet($key){
        if(!$this->open()){
            return null;
        }
        return $this->cache->sMembers($key);
    }

    public function getSetSize($key){
        if(!$this->open()){
            return false;
        }
        return $this->cache->sSize($key);
    }

    public function pushToList($key, $value) {
        if(!$this->open()){
            return false;
        }
        return $this->cache->rPush($key,$value);
    }

    public function popFromList($key) {
        if(!$this->open()){
            return false;
        }
        return $this->cache->lPop($key);
    }

    public function getList($key, $start=0, $end = -1) {
        if(!$this->open()){
            return false;
        }
        return $this->cache->lRange($key,$start,$end);
    }

    public function getListSize($key){
        if(!$this->open()){
            return false;
        }
        return $this->cache->lSize($key);
    }

    public function deleteList($key){
        return $this->delete($key);
    }
}