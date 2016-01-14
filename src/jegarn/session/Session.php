<?php

namespace jegarn\session;

abstract class Session{

    public $serverAddress;
    public $serverPort;
    public $clientAddress;
    public $clientPort;
    public $auth;
    public $userId;
    public $reachable;
    public $fd;
    public $sessionId;

    public function init($serverAddress, $serverPort, $clientAddress, $clientPort, $sessionId, $auth, $userId, $reachable, $fd) {
        $this->serverAddress = $serverAddress;
        $this->serverPort = $serverPort;
        $this->clientAddress = $clientAddress;
        $this->clientPort = $clientPort;
        $this->sessionId = $sessionId;
        $this->auth = $auth;
        $this->userId = $userId;
        $this->reachable = $reachable;
        $this->fd = $fd;
        return $this;
    }

    public function getServerAddress() {
        return $this->serverAddress;
    }

    public function setServerAddress($serverAddress) {
        $this->serverAddress = $serverAddress;
    }

    public function getServerPort() {
        return $this->serverPort;
    }

    public function setServerPort($serverPort) {
        $this->serverPort = $serverPort;
    }

    public function getClientAddress() {
        return $this->clientAddress;
    }

    public function setClientAddress($clientAddress) {
        $this->clientAddress = $clientAddress;
    }

    public function getClientPort() {
        return $this->clientPort;
    }

    public function setClientPort($clientPort) {
        $this->clientPort = $clientPort;
    }

    public function getSessionId() {
        return $this->sessionId;
    }

    public function setSessionId($sessionId) {
        $this->sessionId = $sessionId;
    }

    public function isAuth() {
        return $this->auth;
    }

    public function setAuth($auth) {
        $this->auth = $auth;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function isReachable(){
        return $this->reachable;
    }

    public function setReachable($reachable){
        $this->reachable = $reachable;
    }

    public function getFd() {
        return $this->fd;
    }

    public function setFd($fd) {
        $this->fd = $fd;
    }
}