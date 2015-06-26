<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 下午9:10
 */

namespace minions\config;
use minions\base\SingleInstanceBase;
use minions\event\EventManager;

final class ConfigManager extends SingleInstanceBase{

    public static $server;
    public static $appRoute;
    public static $eventManager;

    public function init(&$config){
        $this->initEventManager($config['eventManager']);
        $this->initServer($config['server']);
        $this->initAppRoute($config['appRoute']);
        $this->initApps($config['apps']);
    }

    public function initEventManager(&$options){
        if(empty(self::$eventManager)){
            self::$eventManager = EventManager::getInstance();
        }
    }

    public function initServer(&$options){
        if(empty(self::$server)){
            $class = $options['class'];
            $obj = $class::getInstance();
            $obj->init($options);
            self::$server = & $obj;
        }
    }

    public function initAppRoute(&$options){
        if(empty(self::$appRoute)){
            $class = $options['class'];
            $obj = $class::getInstance();
            $obj->init($options);
            self::$appRoute = & $obj;
        }
    }

    public function initApps(&$options){
        if(is_array($options)){
            if(!isset($options['appBase'])){
                $options['appBase'] = ['class' => 'minions\\base\\AppBase'];
            }
            foreach($options as $name=>$config){
                if(!isset(self::$name)){
                    self::$name = $config['class']::getInstance();
                    self::$name->init($config);
                }
            }
        }
    }
}