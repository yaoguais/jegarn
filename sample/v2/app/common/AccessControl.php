<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 下午10:41
 */

namespace app\common;
use minions\event\Event;
use minions\event\IReceiveCallback;
use minions\protocol\IProtocol;
use minions\base\SingleInstanceBase;

class AccessControl extends SingleInstanceBase implements IProtocol,IReceiveCallback{

    public function & pack(&$data){

    }

    public function & unpack(&$data){

    }

    public function receive(Event &$event,$fd,&$data){

    }
}