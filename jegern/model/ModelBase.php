<?php

namespace jegern\model;

abstract class ModelBase {

    public static $singleInstance = null;

    public static function getConnection(){

    }

    public static function model(){
        if(isset(static::$singleInstance)){
            if(is_object(static::$singleInstance)){
                return static::$singleInstance;
            }else{
                return static::$singleInstance = new static();
            }
        }
        return new static();
    }

}