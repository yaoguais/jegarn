<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 下午10:52
 */

namespace minions\event;

interface IReceiveCallback{
    public function receive(Event &$event,$fd,&$data);
}