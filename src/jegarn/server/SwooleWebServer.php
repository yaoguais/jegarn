<?php

namespace jegarn\server;

use jegarn\packet\Packet;
use jegarn\session\Session;
use jegarn\manager\SessionManager;
use jegarn\log\Logger;
use swoole_server;
use swoole_buffer;

/**
 * Class SwooleWebServer
 * @package minions\server
 * @property swoole_buffer[]          $fdReadBufferMap
 * @property swoole_buffer[]          $fdWriteBufferMap
 * @property WebsocketBufferProcessor $wsBuffProcessor
 */
class SwooleWebServer extends SwooleServer {

    const TCP               = 0;
    const WEBSOCKET         = 1;
    const WEBSOCKET_ERROR   = 2;
    const WEBSOCKET_SUCCESS = 3;

    protected $wsBuffProcessor;

    /**
     * @param $config
     * @return SwooleServer
     */
    public function initConfig($config) {
        parent::initConfig($config);
        if(isset($config['websocket_type'])){
            $this->wsBuffProcessor = new WebsocketBufferProcessor($config['websocket_type']);
            unset($config['websocket_type']);
        }else{
            $this->wsBuffProcessor = new WebsocketBufferProcessor();
        }
        $this->config = $config;
        return $this;
    }

    public function onSwooleConnect(swoole_server $server, $fd, $from_id){
        Logger::addInfo('connect (fd:' . $fd . ')');
        $this->dispatchConnect($this->getSwooleSession($server, $fd, $from_id));
    }

    public function onSwooleClose(swoole_server $server, $fd, $from_id){
        $this->wsBuffProcessor->destroy($fd);
        parent::onSwooleClose($server, $fd, $from_id);
    }

    public function onSwooleReceive(swoole_server $server, $fd, $from_id, $message){
        if($this->wsBuffProcessor->getPacketInfo($fd, $message, $isWebsocket, $isHandshake, $response)){
            if(!$isWebsocket){
                Logger::addInfo('server message come (fd:' . $fd . ')');
                parent::onSwooleReceive($server, $fd, $from_id, $message);
            }else{
                if($isHandshake){
                    Logger::addInfo('send handshake data (fd:' . $fd . ') data: '.$response);
                    if($response){
                        $this->instance->send($fd, $response);
                        $this->wsBuffProcessor->setHandshakeAlreadySend($fd);
                    }else{
                        Logger::addError('got handshake but response is empty fd(' . $fd . ')');
                    }
                }else{
                    Logger::addInfo('web user message (fd:' . $fd . ')');
                    $this->wsBuffProcessor->append($fd, $message);
                    $this->dealWebsocketPacket($server, $fd, $from_id);
                }
            }
        }else{
            Logger::addError('get packet info failed fd(' . $fd . ')');
        }
    }

    protected function sendPacketOfCurrentProcess(Session $session, Packet $packet){
        $previousReachable = $this->isFdReachable($session->getFd());
        $session->setReachable($previousReachable);
        if(false !== $this->dispatchSend($session, $packet)){
            // if send buffer is full or other reason, send will failed, so second dispatch packet and tell them session not reachable
            $data = $packet->convertToArray();
            $data[$this->sessionKey] = $session->getSessionId();
            $packetStr = json_encode($data);
            $encodedPacketStr = $this->wsBuffProcessor->encode($packetStr);
            if(!$this->instance->send($session->getFd(), $encodedPacketStr) && $previousReachable){
                $session->setReachable(false);
                Logger::addInfo('send directly failed, dispatch again (fd:' . $session->getFd() . ') ');
                $this->dispatchSend($session, $packet);
            }else{
                Logger::addInfo('send directly success (fd:' . $session->getFd() . ') |'.$encodedPacketStr.'|'.$packetStr.'|');
            }
        }else{
            Logger::addInfo('send dispatch failed (fd:' . $session->getFd() . ') ');
        }
    }

    protected function dealWebsocketPacket($server, $fd, $from_id){
        $session = $this->getSwooleSession($server, $fd, $from_id);
        $sessionManager = SessionManager::getInstance();
        for(;;){
            $packetStr = $this->wsBuffProcessor->consumePacket($fd, $response);
            if($response == WebsocketBufferProcessor::$PONG){
                Logger::addInfo('send pong packet : (fd: ' . $fd . ')');
                $this->instance->send($fd, WebsocketBufferProcessor::$PONG);
            }else if($response == WebsocketBufferProcessor::$CLOSE){
                $this->close($session); // browser not close the connection but send a close packet, so close it manual.
            }
            if(is_string($packetStr)){
                if($packet = $this->parsePacket($packetStr, $sessionId, $serverInfo)){
                    Logger::addInfo('parsed packet info: (fd: ' . $fd . ')' . str_replace("\n",' ',var_export($packet, true)). 'session: '.$sessionId . '|serverInfo: '.$serverInfo);
                    if(!$sessionManager->isValidSession($session) && $sessionId && ($fs = $sessionManager->getSessionBySessionId($sessionId))){
                        $session = $fs;
                    }
                    if(false === $this->dispatchReceive($session, $packet)){
                        Logger::addInfo('dispatch receive failed: (fd: ' . $fd . ')');
                    }
                }else{
                    $this->increaseError();
                    Logger::addError('str can not convert packet(fd: ' . $fd . ')[BEGIN]'.$packetStr.'[END]');
                }
            }else if(false === $packetStr){
                $this->increaseError();
                Logger::addError('packet parse failed(fd: ' . $fd . ')');
                break;
            }else{
                break;
            }
        }
    }

    protected function parsePacket($str, &$sessionId, &$serverInfo){
        if(($arr = json_decode($str, true))){
            $sessionId = $serverInfo = null;
            if(isset($arr[$this->sessionKey]) && $arr[$this->sessionKey]){
                $sessionId = $arr[$this->sessionKey];
            }
            if($packet = Packet::getPacketFromArray($arr)){
                return $packet;
            }
        }
        return false;
    }
}