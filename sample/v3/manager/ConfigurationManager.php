<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-26
 * Time: 下午9:18
 */

namespace minions\manager;
use minions\server\ServerManager;
use minions\cache\CacheManager;

final class ConfigurationManager{
    private static $_instance;
    private function __construct(){}
    public function getInstance(){
        return self::$_instance = self::$_instance ? : new self;
    }

    public function init($config){
        $servers = isset($config['server'][0]) ? : [$config['server']];
        $serverManager = ServerManager::getInstance();
        foreach($servers as $server){
            $serverManager->addServer(null,$server);
        }
        $caches = isset($config['cache'][0]) ? : [$config['cache']];
        $cacheManager = 
        foreach($caches as $cache){
            $this->initCache($cache);
        }
    }
    public function initServer($config){

    }
    public function initCache($config){

    }
}