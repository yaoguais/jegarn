<?php

namespace jegarn\manager;
use jegarn\log\Logger;
use swoole_client;

class SocketManager extends BaseManager {

    /**
     * @var swoole_client[]
     */
    protected $clients;

    public static function getInstance($class = __CLASS__){
        return parent::getInstance($class);
    }

    public function closeClient($id){
        if(isset($this->clients[$id])){
            $this->clients[$id] = null;
            unset($this->clients[$id]);
        }
    }

    public function sendClientMessage($host, $port, $message, $options){
        $id = $this->getClientId($host, $port);
        Logger::addInfo('send id: ' . $id . ' message: ' . $message);
        if(empty($message)){
            Logger::addError('send message length 0 id: ' . $id);
            return false;
        }
        if(!isset($this->clients[$id])){
            $ssl = isset($options['ssl_cert_file']) && $options['ssl_cert_file'];
            if($client = new swoole_client($ssl ? SWOOLE_SOCK_TCP | SWOOLE_SSL : SWOOLE_SOCK_TCP)){
                $this->clients[$id] = $client;
            }else{
                $this->addSocketError($id, 'socket create failed');
                return false;
            }
            if(!$client->connect($host, $port, isset($options['timeout']) ? $options['timeout'] : 0.5)){
                $this->addSocketError($id, 'socket connect failed');
                $this->closeClient($id);
                return false;
            }
        }
        $client = $this->clients[$id];
        $retryCount = 3;
        send_data:
        --$retryCount;
        $messageLen = strlen($message);
        $ret = $client->send($message);
        if($ret === $messageLen){
            return true;
        }else if($retryCount <= 0){
            $this->addSocketError($id, 'send message failed and retry out of use');
            $this->closeClient($id);
            return false;
        }else if(false === $ret){
            if(!$client->connect($host, $port, isset($options['timeout']) ? $options['timeout'] : 0.1)){
                $this->addSocketError($id, 'socket reconnect failed');
                $this->closeClient($id);
                return false;
            }
            goto send_data;
        }else/*if($ret !== $messageLen)*/{
            $message = substr($message, $ret);
            $this->addSocketError($id, 'send message completed failed');
            goto send_data;
        }
    }

    protected function addSocketError($id, $preMessage){
        if(isset($this->clients[$id])){
            $errorNo = $this->clients[$id]->errCode;
            $errorStr = socket_strerror($errorNo);
        }else{
            $errorNo = 0;
            $errorStr = '';
        }
        Logger::addError($preMessage . ': ' . $id . ' ' . $errorStr . '[' . $errorNo . ']');
    }

    protected function getClientId($host, $port){
        return $host . ':' . $port;
    }
}