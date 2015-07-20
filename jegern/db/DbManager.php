<?php

namespace jegern\db;

abstract class DbManager {

    protected static $db;

    public static function addDb($dns,$user='root',$pass='',$key=0){

        if(isset(self::$db[$key])){
            self::$db[$key]->close();
            unset(self::$db[$key]);
        }
        self::$db[$key] = new \PDO($dns,$user,$pass);
    }

    public static function getDb($key=0){
        return self::$db[$key];
    }
}