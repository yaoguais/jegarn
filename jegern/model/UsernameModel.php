<?php

namespace jegern\model;

class UsernameModel extends ModelBase {

    public static $singleInstance = true;

    public static function getConnection(){
        $db = CacheManager::getCache('user');
        $db->useDb(1);
        return $db;
    }
}