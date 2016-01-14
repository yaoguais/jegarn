<?php

namespace jegarn\packet;
use jegarn\manager\UserManager;

class AuthPacket extends Packet {

    const STATUS_NEED_AUTH = 1;
    const STATUS_AUTH_SUCCESS = 2;
    const STATUS_AUTH_FAILED  = 3;

    protected $type = 'auth';

    public function getUid(){
        return isset($this->content['uid']) ? $this->content['uid'] : null;
    }

    public function setUid($value){
        $this->content['uid'] = intval($value);
    }

    public function getAccount(){
        return isset($this->content['account']) ? $this->content['account'] : null;
    }

    public function setAccount($value){
        $this->content['account'] = $value;
    }

    public function getPassword(){
        return isset($this->content['password']) ? $this->content['password'] : null;
    }

    public function setPassword($value){
        $this->content['password'] = $value;
    }

    public function getStatus(){
        return isset($this->content['status']) ? $this->content['status'] : null;
    }

    public function setStatus($value){
        $this->content['status'] = $value;
    }

    public function auth(){
        $um = UserManager::getInstance();
        if($user = $um->getUser($this->getAccount())){
            if($um->authPassword($this->getPassword(), $user['password'])){
                $this->setUid($user['uid']);
                return true;
            }
        }
        return false;
    }
}