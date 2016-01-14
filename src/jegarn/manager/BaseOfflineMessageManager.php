<?php

namespace jegarn\manager;
use jegarn\cache\Cache;
use jegarn\util\ConvertUtil;
use jegarn\packet\Packet;

abstract class BaseOfflineMessageManager extends BaseManager{

    public function addMessage($uid, $message){
        Cache::getInstance()->rPush($this->getCacheKey($uid), $message);
    }

    public function getPacket($uid){
        if($message = Cache::getInstance()->lPop($this->getCacheKey($uid))){
            return ConvertUtil::unpack($message);
        }
        return null;
    }

    public function addPacket($uid, Packet $packet){
        Cache::getInstance()->rPush($this->getCacheKey($uid), ConvertUtil::pack($packet));
    }

    public function getAllMessage($uid){
        Cache::getInstance()->lRange($this->getCacheKey($uid), 0, -1);
    }

    public function removeAllMessage($uid){
        Cache::getInstance()->del($this->getCacheKey($uid));
    }

    public function getMessageCount($uid){
        Cache::getInstance()->lSize($this->getCacheKey($uid));
    }

    abstract protected function getCacheKey($id);
}