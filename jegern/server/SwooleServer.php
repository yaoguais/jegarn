<?php

namespace jegern\server;

class SwooleServer{

    protected $server;
    protected $bufferDriverClass;
    protected $fdBufferMap = [];

    public function __construct($host,$ip,$config,$bufferDriverClass){
        $server = new \swoole_server($host, $ip, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        $server->set($config);
        $server->on('receive',array($this,'onReceive'));
        $server->on('close',array($this,'onClose'));
        $this->server = & $server;
        $this->bufferDriverClass = $bufferDriverClass;
    }

    protected function initFdBuffer($fd,$size=102400){
        if(isset($this->fdBufferMap[$fd])){
            return;
        }
        $class = $this->bufferDriverClass;
        $this->fdBufferMap[$fd] = new $class();
        $this->fdBufferMap[$fd]->init($size);
    }

    public function onReceive(\swoole_server $server, $fd, $from_id, $message){
        $oef = substr($message,-2);
        if($oef!=="\r\n"){
            $this->initFdBuffer($fd);
            $this->fdBufferMap[$fd]->append($message);
        }else{
            if(isset($this->fdBufferMap[$fd])){
                $this->fdBufferMap[$fd]->append($message);
                $message = $this->fdBufferMap[$fd]->get();
                $this->fdBufferMap[$fd]->clear();
            }
            echo $message,"\n";
        }
    }

    public function onClose(\swoole_server $server, $fd,$from_id){
        if(isset($this->fdBufferMap[$fd])){
            $this->fdBufferMap[$fd]->destroy();
            $this->fdBufferMap[$fd] = null;
        }
    }

    public function start(){
        $this->server->start();
    }
}