<?php

use \Yaf\Dispatcher;
use \minions\yaf\ApiPluginBase;
use \minions\manager\DbManager;

class Bootstrap extends Yaf\Bootstrap_Abstract{

    public function _initDb(){

        DbManager::getInstance()->initConfig(\Yaf\Application::app()->getConfig()->get('application')->get('database'));
    }

    public function _initPlugin(Dispatcher $dispatcher) {

        $dispatcher->registerPlugin(new ApiPluginBase());
    }
}