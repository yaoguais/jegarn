<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 下午9:44
 */

namespace app\chat;
use minions\app\AppBase;
use minions\event\Event;
use minions\event\IReceiveCallback;

class Chat extends AppBase implements IReceiveCallback{

    public function receive(Event &$event,$fd,&$data){
        echo $data;
    }
}