<?php

namespace jegern\pack;

final class PackManager{
    const ERROR_PACK = ' ';
    const PHP_PACK = '0';
    const MSG_PACK = '1';
    protected static $objMap = [
        self::MSG_PACK => 'fatty\\MsgPack',
        self::PHP_PACK => 'fatty\\PhpPack'
    ];
    public static function getPack($id){
        if(isset(self::$objMap[$id])){
            return new self::$objMap[$id]();
        }
        return null;
    }
    public static function getId($pack){
        $packClass = get_class($pack);
        foreach(self::$objMap as $id=>$class){
            if($class == $packClass){
                return $id;
            }
        }
        return self::ERROR_PACK;
    }
    public static function hasId($id){
        return isset(self::$objMap[$id]);
    }
}