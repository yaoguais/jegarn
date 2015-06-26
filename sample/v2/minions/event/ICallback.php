<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 下午8:50
 */

namespace minions\event;

interface ICallback{
    public function connect(Event &$event,$fd);
    public function receive(Event &$event,$fd);
    public function send(Event &$event,$fd,&$data);
    public function close(Event &$event,$fd);
}