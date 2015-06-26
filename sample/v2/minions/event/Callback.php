<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 下午6:44
 */

namespace minions\event;
use minions\base\SingleInstanceBase;

class Callback extends SingleInstanceBase{

    public function connect($fd){
        return EventManager::getInstance()->dispatchEvent(EventName::CONNECT,$fd);
    }

    public function receive($fd,&$data){
        return EventManager::getInstance()->dispatchEvent(EventName::RECEIVE,$fd,$data);
    }

    public function send($fd,&$data){
        return EventManager::getInstance()->dispatchEvent(EventName::SEND,$fd,$data);
    }

    public function close($fd){
        return EventManager::getInstance()->dispatchEvent(EventName::CLOSE,$fd);
    }
}