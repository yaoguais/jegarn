<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-26
 * Time: 下午9:11
 */

namespace minions\server;

final class SwooleServer implements IServer{
    private $_app;
    public function init($config){
        $app = new \swoole_server($config['host'],$config['port'],$config['mode'],$config['sock_type']);
        $app->set($config);
        $app->on('connect',[$this,'onConnect']);
        $app->on('receive',[$this,'onReceive']);
        $app->on('close',[$this,'onClose']);
        $this->_app = &$app;
    }
    public function start(){
        $this->_app->start();
    }
    public function onConnect($server,$fd,$fromId){
        $this->connect($fd);
    }
    public function connect($fd){
        echo "connect $fd \n";
    }
    public function onReceive($server,$fd,$fromId,$data){
        $this->receive($fd,$data);
    }
    public function receive($fd,&$data){
        echo "receive $fd $data\n";
    }
    public function send($fd,&$data){
        return $this->_app->send($fd,$data);
    }
    public function onClose($server,$fd,$fromId){
        echo "on close $fd\n";
    }
    public function close($fd){
        $this->_app->close($fd);
    }
    public function stop(){}
}