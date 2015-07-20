<?php

namespace jegern\app;

abstract class AppManager{

    const APP_SYSTEM = '0';
    const APP_AUTHOR = '1';
    const APP_CHAT = '2';
    const APP_GROUP = '3';
    protected static $appObject;

    public static function dispatcherApp(){

    }

    public static function getApp($appId){
        if(isset(self::$appObject[$appId])){
            return self::$appObject[$appId];
        }
        $app = null;
        switch($appId){
            case self::APP_CHAT:
                $app = new Chat();
                break;
            case self::APP_SYSTEM:
                $app = new System();
                break;
            case self::APP_AUTHOR:
                $app = new Author();
                break;
            case self::APP_GROUP:
                $app = new Group();
                break;
        }
        if(null !== $app){
            $app->init();
            return $app;
        }
        return null;
    }
}