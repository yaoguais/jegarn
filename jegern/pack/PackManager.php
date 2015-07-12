<?php

namespace jegern\pack;

final class PackManager{

    private static $_packObjects;

    public static function addPack($id,&$pack){
        if(!isset(self::$_packObjects[$id])){
            self::$_packObjects[$id] = $pack;
            return true;
        }
        return false;
    }

    public static function getPack($id,$class=null){
        if(isset(self::$_packObjects[$id])){
            return self::$_packObjects[$id];
        }
        if($class != null && class_exists($class)){
            $pack = new $class();
            return self::addPack($id,$pack) ? $pack : null;
        }
        return null;
    }

    public static function removePack($id){
        if(isset(self::$_packObjects[$id])){
            unset(self::$_packObjects[$id]);
            return true;
        }
        return false;
    }
}