<?php

namespace jegern\server;

abstract class UserManager {

    protected static $fdUserMap;

    public static function addUser($fd){

        self::$fdUserMap[$fd] = true;
    }

    public static function removeUser($fd){

        if(isset(self::$fdUserMap[$fd])){
            unset(self::$fdUserMap[$fd]);
        }
    }

    public static function hasUser($fd){

        return isset(self::$fdUserMap[$fd]);
    }
}