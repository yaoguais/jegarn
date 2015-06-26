<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 下午12:42
 */

namespace minions\server;

interface IServer{
    public function init($options);
    public function start();
    public function send($fd,&$data);
    public function close($fd);
}