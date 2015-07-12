<?php

namespace jegern\model;
use jegern\cache\CacheManager;

class ConfigurationModel extends ModelBase {

    public static $singleInstance = true;

    public function getConnection(){
        $db = CacheManager::getCache('configuration');
        $db->useDb(15);
        return $db;
    }

    public function getNextUid(){
        return $this->getConnection()->increase('uid');
    }

    public function getNextGid(){
        return $this->getConnection()->increase('gid');
    }
}