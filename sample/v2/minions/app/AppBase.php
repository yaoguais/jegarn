<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 下午9:31
 */

namespace minions\app;
use minions\base\SingleInstanceBase;
use minions\config\ConfigManager;

class AppBase extends SingleInstanceBase implements IApp{

    protected static $inited = false;

    public function init($options){
        if(!self::$inited){
            self::$inited = true;
            $instance = self::getInstance();
            $em = EventManager::getInstance();
            $em->addEvent(new Event($instance,EventName::RECEIVE,[ConfigManager::$appRoute,'receive'],true));
        }
    }
}