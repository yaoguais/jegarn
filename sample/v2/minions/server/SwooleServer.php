<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 下午12:55
 */

namespace minions\server;
use minions\base\SingleInstanceBase;
use minions\event\Event;
use minions\event\EventManager;
use minions\event\Callback;

final class SwooleServer extends SingleInstanceBase implements IServer{
    private static $app;
    public function init($options){
        $app = new swoole_server($options['host'],$options['port'],$options['mode'],$options['sock_type']);
        $app->set($options);
        $em = EventManager::getInstance();
        $em->attachEvent(new Event($app,'connect','SwooleServer::onConnect',true),'on');
        $em->attachEvent(new Event($app,'receive','SwooleServer::onReceive',true),'on');
        $em->attachEvent(new Event($app,'close','SwooleServer::onClose',true),'on');
        self::$app = &$app;
    }

    public function start(){
        self::$app->start();
    }

    public static function onConnect($server,$fd,$fromId){
        Callback::getInstance()->connect($fd);
    }

    public static function onReceive($server,$fd,$fromId,$data){
        Callback::getInstance()->receive($fd,$data);
    }

    public static function onClose($server,$fd,$fromId){
        Callback::getInstance()->close($fd);
    }

    public function send($fd,&$data){
        if(false !==Callback::getInstance()->send($fd,$data)){
            self::$app->send($fd,$data);
        }
    }

    public function close($fd){
        if(false !== Callback::getInstance()->close($fd)){
            self::$app->close($fd);
        }
    }
}