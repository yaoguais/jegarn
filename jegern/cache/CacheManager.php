<?php

namespace jegern\cache;

class CacheManager {

    private static $_cacheObjects;

    public static function addCache($name,&$cache){
        self::$_cacheObjects[$name] = $cache;
    }

    public static function removeCache($name){
        if(isset(self::$_cacheObjects[$name])){
            $cache = self::$_cacheObjects[$name];
            unset(self::$_cacheObjects[$name]);
            return $cache;
        }else{
            return null;
        }
    }

    public static function getCache($name){
        if(isset(self::$_cacheObjects[$name])){
            return self::$_cacheObjects[$name];
        }
        return null;
    }
}