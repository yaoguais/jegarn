<?php

namespace jegern\model;
use jegern\cache\CacheManager;
use jegern\pack\PackManager;

class UserConnectionModel extends ModelBase {

    public static $singleInstance = true;

    public static $packId = 'uc';

    public function getConnection(){
        $db = CacheManager::getCache('user_connection');
        $db->useDb(2);
        return $db;
    }

    public function addUser($uid,$host,$port,$processId,$fd){
        $packObject = PackManager::getPack(self::$packId,'jegern\\pack\\GluePack');
        $idInfo = [$host,$port,$processId,$fd];
        return $this->getConnection()->set($uid,$packObject->pack($idInfo));
    }

    public function removeUser($uid){
        return $this->getConnection()->delete($uid);
    }

    public function getUser($uid){
        if(!($id = $this->getConnection()->get($uid))){
            return false;
        }
        $packObject = PackManager::getPack(self::$packId,'jegern\\pack\\GluePack');
        return $packObject->unpack($id);
    }
}