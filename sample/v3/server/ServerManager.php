<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-26
 * Time: 下午9:20
 */

namespace minions\server;

final class ServerManager{
    private static $_instance;
    private function __construct(){}
    public static function getInstance(){
        return self::$_instance = self::$_instance ? : new self;
    }
    private $_servers = [];

    public function addServer($key,$config){
        $key = $key ? : (isset($config['key']) ? $config['key'] : count($this->_servers));
        if(empty($config['class']) || isset($this->_servers[$key]) || !class_exists($config['class'])){
            return false;
        }
        $server = new $config['class']();
        $server->init($config);
        $this->_servers[$key] = &$server;
        return true;
    }

    public function removeServer($key){
        if(null === $key){
            foreach($this->_servers as &$server){
                $server->stop();
            }
            $this->_servers = [];
        }else if(is_string($key)){
            if(isset($this->_servers[$key])){
                $this->_servers[$key]->stop();
                unset($this->_servers[$key]);
            }
        }else if(is_array($key)){
            foreach($key as $k){
                if(isset($this->_servers[$k])){
                    $this->_servers[$k]->stop();
                    unset($this->_servers[$k]);
                }
            }
        }else{
            return false;
        }
        return true;
    }

    public function startServers(){
        if(is_array($this->_servers)){
            foreach($this->_servers as $server){
                $server->start();
            }
        }
    }
}