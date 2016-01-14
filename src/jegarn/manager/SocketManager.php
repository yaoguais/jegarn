<?php

namespace jegarn\manager;
use jegarn\log\Logger;

class SocketManager extends BaseManager {

    protected $clients;

    public static function getInstance($class = __CLASS__){
        return parent::getInstance($class);
    }

    public function closeClient($host, $port){
        $id = $this->getClientId($host, $port);
        if(isset($this->clients[$id])){
            if(is_resource($this->clients[$id])){
                fclose($this->clients[$id]);
            }
            unset($this->clients[$id]);
        }
    }

    public function sendClientMessage($host, $port, $message){
        $id = $this->getClientId($host, $port);
        Logger::addInfo('send id: '.$id. ' message: '.$message);
        if(empty($message)){
            Logger::addError('send message length 0 id: '.$id);
            return false;
        }
        if(!isset($this->clients[$id])){
            if($fd = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)){
                $this->clients[$id] = $fd;
            }else{
                $this->addSocketError($id, 'socket create failed');
                return false;
            }
            if(!socket_connect($fd, $host, $port)){
                $this->addSocketError($id, 'socket connect failed');
                return false;
            }
        }
        $fd = $this->clients[$id];
        $retryCount = 3;
        send_data:
        --$retryCount;
        $messageLen = strlen($message);
        $ret = socket_write($fd,$message, $messageLen);
        if($ret === $messageLen){
            return true;
        }else if($retryCount <= 0){
            $this->addSocketError($id, 'send message failed and retry out of use');
            return false;
        }else if(false === $ret){
            if(!socket_connect($fd, $host, $port)){// socket sometimes would be error, so reconnect and send
                $this->addSocketError($id, 'socket reconnect failed');
                return false;
            }
            goto send_data;
        }else/* if($ret !== $messageLen)*/{
            $message = substr($message, $ret);
            $this->addSocketError($id, 'send message completed failed');
            goto send_data;
        }
    }

    protected function addSocketError($id, $preMessage){
        $errorNo = socket_last_error();
        $errorStr = socket_strerror($errorNo);
        Logger::addError($preMessage.': '.$id.' '.$errorStr.'['.$errorNo.']');
    }

    protected function getClientId($host, $port){
        return $host . ':' . $port;
    }
}