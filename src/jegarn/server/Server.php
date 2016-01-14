<?php

namespace jegarn\server;
use jegarn\cache\Cache;
use jegarn\manager\PacketManager;
use jegarn\manager\SessionManager;
use jegarn\session\Session;
use jegarn\packet\Packet;
use jegarn\util\ConvertUtil;
use jegarn\util\TextUtil;

abstract class Server{

    protected $instance        = null;
    protected $running         = false;
    protected $config          = null;
    protected $serverId        = null;
    protected $connectCount    = 0;
    protected $receiveCount    = 0;
    protected $sendCount       = 0;
    protected $closeCount      = 0;
    protected $errorCount      = 0;
    protected $lastFlushTime   = 0;
    protected $flushInterval   = 120;
    protected $registerKey     = 'L_server';
    protected $serverKey       = 'server_info';
    protected $sessionKey      = 'session_id';

    abstract public function send(Session $session, Packet $packet);
    abstract public function close(Session $session);
    abstract public function start();
    abstract public function isSessionReachable(Session $session);

    public function initConfig($config){
        if(isset($config['flush_interval'])) $this->flushInterval = $config['flush_interval'];
        unset($config['flush_interval']);
        $this->config = $config;
        return $this;
    }

    public function dispatchConnect(Session $session){
        $this->increaseConnect();
        return SessionManager::getInstance()->filterSession($session);
    }

    public function dispatchReceive(Session $session, Packet $packet) {
        $this->increaseReceive();
        $receive = PacketManager::getInstance()->dispatchReceive($packet, $session);
        // if session auth passed, and session is new coming or reconnect, dispatch offline message
        if($session->isAuth()){
            $sessionManager = SessionManager::getInstance();
            if($sessionManager->isNewSession($session)){
                $sessionManager->clearNewSession($session);
                PacketManager::getInstance()->dispatchOffline($session);
            }
        }
        return $receive;
    }

    public function dispatchSend(Session $session, Packet $packet){
        $this->increaseSend();
        return PacketManager::getInstance()->dispatchSend($packet, $session);
    }

    public function dispatchClose(Session $session){
        $this->increaseClose();
        return SessionManager::getInstance()->removeSession($session);
    }

    public function register(){
        if(null === $this->serverId) $this->serverId = TextUtil::generateGUID();
        Cache::getInstance()->hSet($this->registerKey, $this->serverId, $this->config['host'] . ':' . $this->config['port']);
        $this->modifyServerInfo();
    }

    public function unRegister(){
        if(null !== $this->serverId){
            $cache = Cache::getInstance();
            $cache->hDel($this->registerKey, $this->serverId);
            $cache->del('l_' . $this->config['host'] . ':' . $this->config['port']);
        }
    }

    public function getRegister($serverId){
        return Cache::getInstance()->hGet($this->registerKey, $serverId);
    }

    public function getAllRegister(){
        return Cache::getInstance()->hGetAll($this->registerKey);
    }

    public function getServerInfo(){
        return Cache::getInstance()->hGetAll('l_' . $this->config['host'] . ':' . $this->config['port']);
    }

    public function getAllServerInfo(){
        $cache = Cache::getInstance();
        if($bindServerIdList = $cache->hVals($this->registerKey)){
            $list = [];
            foreach($bindServerIdList as $bindServerId){
                $serverInfo = $cache->hGetAll('l_' . $bindServerId);
                list($serverInfo['host'], $serverInfo['port']) = explode(':', $bindServerId);
                $list[] = $serverInfo;
            }
            return $list;
        }else{
            return null;
        }
    }

    public function increaseConnect(){
        ++$this->connectCount;
        $this->intervalModifyServerInfo();
    }

    public function increaseClose(){
        ++$this->closeCount;
        $this->intervalModifyServerInfo();
    }

    public function increaseError(){
        ++$this->errorCount;
        $this->intervalModifyServerInfo();
    }

    public function increaseReceive(){
        ++$this->receiveCount;
        $this->intervalModifyServerInfo();
    }

    public function increaseSend(){
        ++$this->sendCount;
        $this->intervalModifyServerInfo();
    }

    public function shutdown(){
        $this->running = false;
        $this->unRegister();
    }

    protected function isConnectedToThisServer(Session $session){
        return $session->getServerPort() == $this->config['port'] && $session->getServerAddress() == $this->config['host'];
    }

    protected function isConnectedToNoneServer(Session $session){
        return !$session->getServerAddress() || !$session->getServerPort();
    }

    protected function parsePacket($str, &$sessionId, &$serverInfo){
        $sessionId = $serverInfo = null;
        if(($arr = ConvertUtil::unpack($str))){
            if(isset($arr[$this->serverKey])){
                if(!($serverInfo = $this->getRegister($arr[$this->serverKey]))){
                    return false;
                }
            }
            if(isset($arr[$this->sessionKey]) && $arr[$this->sessionKey]){
                $sessionId = $arr[$this->sessionKey];
            }
            if($packet = Packet::getPacketFromArray($arr)){
                return $packet;
            }
        }
        return false;
    }

    private function modifyServerInfo(){
        $bindServerId = 'l_' . $this->config['host'] . ':' . $this->config['port'];
        $cache = Cache::getInstance();
        $cache->hSet($bindServerId, 'connect', $this->connectCount);
        $cache->hSet($bindServerId, 'receive', $this->receiveCount);
        $cache->hSet($bindServerId, 'send', $this->sendCount);
        $cache->hSet($bindServerId, 'close', $this->closeCount);
        $cache->hSet($bindServerId, 'error', $this->errorCount);
        $cache->hSet($bindServerId, 'memory', memory_get_peak_usage(true));
        $this->lastFlushTime = time();
    }

    private function intervalModifyServerInfo(){
        if($this->flushInterval <= 0 || time() - $this->lastFlushTime > $this->flushInterval){
            $this->modifyServerInfo();
        }
    }
}