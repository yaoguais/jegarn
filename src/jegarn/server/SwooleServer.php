<?php

namespace jegarn\server;

use jegarn\cache\Cache;
use jegarn\manager\SessionManager;
use jegarn\packet\Packet;
use jegarn\session\Session;
use jegarn\manager\SocketManager;
use jegarn\util\ConvertUtil;
use swoole_server;
use jegarn\log\Logger;
use jegarn\session\SwooleSession;

/**
 * Class SwooleServer
 * @package minions\server
 * @property \swoole_server         $instance
 * @property HeaderNBufferProcessor $rBuffProcessor
 */
class SwooleServer extends Server{

    protected $rBuffProcessor;

    public function initConfig($config){
        $headerSize = isset($config['header_size']) ? intval($config['header_size']) : 4;
        $packChar = isset($config['pack_char']) ? $config['pack_char'] : 'N';
        $defaultBufferSize = isset($config['default_buffer_size']) ? intval($config['default_buffer_size']) : 2048;
        $maxPacketLength = isset($config['max_packet_length']) ? intval($config['max_packet_length']) : 2048;
        unset($config['header_size'], $config['pack_char'], $config['default_buffer_size'], $config['max_packet_length']);
        $this->rBuffProcessor = new HeaderNBufferProcessor($headerSize, $packChar, $defaultBufferSize, $maxPacketLength);
        parent::initConfig($config);
        return $this;
    }

    public function onSwooleConnect(swoole_server $server, $fd, $from_id){
        Logger::addInfo('connect (fd:' . $fd . ')');
        $this->dispatchConnect($this->getSwooleSession($server, $fd, $from_id));
    }

    public function onSwooleClose(swoole_server $server, $fd, $from_id){
        Logger::addInfo('close onSwooleClose(fd:' . $fd . ')');
        $this->rBuffProcessor->destroy($fd);
        SessionManager::getInstance()->removeSessionByFd($fd);
    }

    public function onSwooleReceive(swoole_server $server, $fd, $from_id, $message){
        $this->rBuffProcessor->append($fd, $message);
        $session = $this->getSwooleSession($server, $fd, $from_id);
        $sessionManager = SessionManager::getInstance();
        for(;;){
            $packetStr = $this->rBuffProcessor->consumePacket($fd);
            if(is_string($packetStr)){
                if($packet = self::parsePacket($packetStr, $sessionId, $serverInfo)){
                    Logger::addInfo('parsed packet info: (fd: ' . $fd . ')' . str_replace("\n", ' ', var_export($packet, true)) . 'session: ' . $sessionId . '|serverInfo: ' . $serverInfo);
                    if($serverInfo){ // internal server can send to any user and any where
                        if($toUserSession = $sessionManager->getSessionByUserId($packet->getTo())){
                            Logger::addInfo('internal server toUser online: (fd: ' . $fd . ')');
                            $this->send($toUserSession, $packet);
                        }else{
                            Logger::addInfo('internal server toUser not online: (fd: ' . $fd . ')');
                            $toUserSession = new SwooleSession();
                            $toUserSession->setAuth(true);
                            $toUserSession->setUserId($packet->getTo());
                            $this->send($toUserSession, $packet);
                        }
                    }else{
                        if($packet->isFromSystemUser()){
                            Logger::addInfo('fake user deny: (fd: ' . $fd . ')');
                            break;
                        }else{
                            // come from socket, and it must be the same user, so if is a valid session. don't need to get from cache
                            if(!$sessionManager->isValidSession($session) && ($fs = $sessionManager->getSessionBySessionId($sessionId))){
                                $session = $fs;
                            }
                            if(false === $this->dispatchReceive($session, $packet)){
                                Logger::addInfo('dispatch receive failed: (fd: ' . $fd . ')');
                                break;
                            }
                        }
                    }
                }else{
                    $this->increaseError();
                    Logger::addError('str can not convert packet[SwooleServer](fd: ' . $fd . ')[BEGIN]'.$packetStr.'[END]');
                    continue;
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

    public function onSwoolePipeMessage(/** @noinspection PhpUnusedParameterInspection */swoole_server $server, $from_worker_id, $message){
        /**
         * @var SwooleSession $session
         * @var Packet $packet
         */
        Logger::addInfo('got pipe message');
        $sessionInfo = unpack('N', substr($message, 0, 4));
        $packetInfo = unpack('N', substr($message,4,4));
        if(isset($sessionInfo[1], $packetInfo[1]) && strlen($message) == $sessionInfo[1] + $packetInfo[1] + 8){
            if($session = ConvertUtil::unpack(substr($message,8,$sessionInfo[1]))){
                if($packet = ConvertUtil::unpack(substr($message,8+$sessionInfo[1]))){
                    $this->sendPacketOfCurrentProcess($session, $packet);
                }else{
                    Logger::addError('pipe crashed packet info');
                }
            }else{
                Logger::addError('pipe crashed session info');
            }
        }else{
            Logger::addError('pipe crashed length info');
        }
    }

    protected function sendPacketOfCurrentProcess(Session $session, Packet $packet){
        $previousReachable = $this->isFdReachable($session->getFd());
        $session->setReachable($previousReachable);
        if(false !== $this->dispatchSend($session, $packet)){
            // if send buffer is full or other reason, send will failed, so second dispatch packet and tell them session not reachable
            $data = $packet->convertToArray();
            $data[$this->sessionKey] = $session->getSessionId();
            $packetStr = ConvertUtil::pack($data);
            if(!$this->instance->send($session->getFd(), pack('N', strlen($packetStr)) . $packetStr) && $previousReachable){
                Logger::addInfo('send directly failed, dispatch again (fd:' . $session->getFd() . ') ');
                $session->setReachable(false);
                $this->dispatchSend($session, $packet);
            }else{
                Logger::addInfo('send directly success (fd:' . $session->getFd() . ') ');
            }
        }else{
            Logger::addInfo('send dispatch failed (fd:' . $session->getFd() . ') ');
        }
    }

    public function send(Session $session, Packet $packet){
        $server = $this->instance;
        /* @var SwooleSession $session */
        if($this->isConnectedToThisServer($session)){
            if($server->worker_id === $session->getWorkerId()){
                $this->sendPacketOfCurrentProcess($session, $packet);
            }else{
                Logger::addInfo('send pipe message (fd:' . $session->getFd() . ') ');
                $sessionStr = ConvertUtil::pack($session);
                $packetStr = ConvertUtil::pack($packet);
                $str = pack('N', strlen($sessionStr)) . pack('N', strlen($packetStr)) . $sessionStr . $packetStr;
                $server->sendMessage($str, $session->getWorkerId());
            }
        }else if($this->isConnectedToNoneServer($session)){
            Logger::addInfo('session not connect to any server (user id:' . $session->getUserId() . ') ');
            $session->setReachable(false);
            $this->dispatchSend($session, $packet);
        }else{
            Logger::addInfo('send to remote server (fd:' . $session->getFd() . ') ');
            $this->sendToRemoteServer($session, $packet);
        }
    }

    public function sendToRemoteServer(Session $session, Packet $packet){
        $data = $packet->convertToArray();
        $data[$this->serverKey] = $this->serverId;
        $packetStr = ConvertUtil::pack($data);
        $ret = SocketManager::getInstance()->sendClientMessage($session->getServerAddress(), $session->getServerPort(), pack('N', strlen($packetStr)) . $packetStr, $this->config);
        if(false === $ret){
            Logger::addInfo('send remote server failed, dispatch again (fd:' . $session->getFd() . ') ');
            $session->setReachable(false);
            $this->dispatchSend($session, $packet);
        }
    }

    public function close(Session $session){
        $fd = $session->getFd();
        Logger::addInfo('close manual(fd:' . $fd . ')');
        $this->instance->close($fd);
    }

    public function start(){
        if(!$this->running){
            $ssl = isset($this->config['ssl_cert_file']) && $this->config['ssl_cert_file'];
            $server = new swoole_server($this->config['host'], $this->config['port'], SWOOLE_PROCESS, $ssl ? SWOOLE_SOCK_TCP | SWOOLE_SSL : SWOOLE_SOCK_TCP);
            $server->set($this->config);
            $server->on('connect', [$this, 'onSwooleConnect']);
            $server->on('receive', [$this, 'onSwooleReceive']);
            $server->on('close', [$this, 'onSwooleClose']);
            $server->on('PipeMessage', [$this, 'onSwoolePipeMessage']);
            $this->register();
            $this->instance = &$server;
            Cache::getInstance()->preventProcessShare();
            $this->running = true;
            $this->instance->start();
        }
    }

    public function isSessionReachable(Session $session){
        /* @var SwooleSession $session */
        return $session && $this->isFdReachable($session->getFd());
    }

    public function __destruct(){
        $this->shutdown();
    }

    protected function isFdReachable($fd){
        return $fd && false !== $this->instance->connection_info($fd);
    }

    /**
     * @param \swoole_server $server
     * @param                $fd
     * @param                $from_id
     * @return SwooleSession
     */
    protected function getSwooleSession(swoole_server $server, $fd, $from_id){
        $info = $server->connection_info($fd, $from_id);
        $session = new SwooleSession();
        return $session->init($server->setting['host'], $server->setting['port'], $info['remote_ip'], $info['remote_port'], null, false, null, true, $fd, $server->worker_id);
    }
}