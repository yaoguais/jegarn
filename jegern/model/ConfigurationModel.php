<?php

namespace jegern\model;
use jegern\cache\CacheManager;

class ConfigurationModel extends ModelBase {

    public static $singleInstance = true;

    public static function getConnection(){
        $db = CacheManager::getCache('configuration');
        $db->useDb(15);
        return $db;
    }

    public function getNextUid(){
        $db = $this->getConnection();
        return $db->increase('uid');
    }

    public function getNextGid(){
        $db = $this->getConnection();
        return $db->increase('gid');
    }
}