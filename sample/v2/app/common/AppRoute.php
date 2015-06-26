<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 下午10:41
 */

namespace app\common;
use minions\config\ConfigManager;
use minions\event\Event;
use minions\event\EventName;
use minions\event\IReceiveCallback;
use minions\base\SingleInstanceBase;

class AppRoute extends SingleInstanceBase implements IReceiveCallback{

    public function receive(Event &$event,$fd,&$data){
        $app = substr($data,0,2);
        $data = substr($data,2);
        $map = [
            '00' => 'app\\chat\\Chat',
            '01' => 'app\\chatroom\\ChatRoom'
        ];
        if(isset($map[$app])){
            $class = $map[$app];
            $obj = $class::getInstance();
            ConfigManager::$eventManager->triggerEvent(new Event($this,EventName::RECEIVE,[$obj,'receive'],false),$fd,$data);
        }
    }
}