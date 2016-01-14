<?php

namespace jegarn\session;

class SwooleSession extends Session{

    public $workerId;

    public function init($serverAddress, $serverPort, $clientAddress, $clientPort, $sessionId, $auth, $userId, $reachable, $fd, $workerId) {
        parent::init($serverAddress, $serverPort, $clientAddress, $clientPort, $sessionId, $auth, $userId, $reachable, $fd);
        $this->workerId = $workerId;
        return $this;
    }

    public function getWorkerId() {
        return $this->workerId;
    }

    public function setWorkerId($workerId) {
        $this->workerId = $workerId;
    }
}