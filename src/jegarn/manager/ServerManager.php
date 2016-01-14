<?php

namespace jegarn\manager;
use jegarn\server\Server;

class ServerManager extends BaseManager {

    protected $server;

    /**
     * @param null|string $class
     * @return ServerManager|\jegarn\server\SwooleServer
     * @throws \Exception
     */
    public static function getInstance($class = __CLASS__){
        return parent::getInstance($class);
    }

    /**
     * @param Server $server
     * @return \jegarn\server\SwooleServer
     */
    public function addServer(Server $server){
        $this->server = $server;
        return $this;
    }

    public function __call($name, $arguments){

        return call_user_func_array([$this->server, $name], $arguments);
    }
}