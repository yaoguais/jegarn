<?php

namespace jegern\model;
use jegern\cache\CacheManager;

class MessageRecordModel extends ModelBase{

    public static $singleInstance = true;

    public function getConnection(){
        $db = CacheManager::getCache('message_record');
        $db->useDb(5);
        return $db;
    }

    public function addMessage($uid,$message){
        return $this->getConnection()->append($uid,$message);
    }

    public function getMessageList($uid){
        return $this->getConnection()->get($uid);
    }
}