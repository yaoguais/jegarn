<?php

use jegarn\cache\Cache;
use jegarn\server\Server;
use \Yaf\Dispatcher;
use \minions\http\ApiPluginBase;
use \minions\db\Db;

// include server sdk
require_once __DIR__ . '/../../../../sdk/php/src/jegarn.php';

class Bootstrap extends Yaf\Bootstrap_Abstract{

    public function _initDb(){

        Db::getInstance()->initConfig(\Yaf\Application::app()->getConfig()->get('application')->get('database'));
    }

    public function _initPlugin(Dispatcher $dispatcher) {

        $dispatcher->registerPlugin(new ApiPluginBase());
    }

    public function _initServer(){
        Cache::getInstance()->initConfig(\Yaf\Application::app()->getConfig()->get('application')->get('cache'));
        Server::getInstance()->initConfig(\Yaf\Application::app()->getConfig()->get('application')->get('chat'))->register();
    }
}