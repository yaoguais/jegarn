<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-26
 * Time: 下午9:13
 */

namespace minions\server;

interface IServer{
    public function init($config);
    public function start();
    public function connect($fd);
    public function receive($fd,&$data);
    public function send($fd,&$data);
    public function close($fd);
    public function stop();
}