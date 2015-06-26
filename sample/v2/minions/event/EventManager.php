<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 上午7:59
 */

namespace minions\event;
use minions\base\SingleInstanceBase;

final class EventManager extends SingleInstanceBase implements IEventManager{

    private $_events;

    public function addEvent(Event &$event){
        if(empty($event->name)){
            return false;
        }
        $this->_events[$event->name][] = & $event;
        return true;
    }

    public function removeEvent(Event &$event){
        if(empty($this->_events[$event->name])){
            return false;
        }
        $events =  & $this->_events[$event->name];
        $found = false;
        foreach($events as $i=>&$e){
            if($e === $event){
                unset($events[$i]);
                $found = true;
            }
        }
        return $found;
    }

    public function hasEvent(Event &$event){
        if(empty($this->_events[$event->name])){
            return false;
        }
        $events =  & $this->_events[$event->name];
        $found = false;
        foreach($events as &$e){
            if($e === $event){
                $found = true;
                break;
            }
        }
        return $found;
    }

    public function triggerEvent(Event & $event){
        $args = func_get_args();
        $args[0] = & $event;
        self::_executeEventCallback($event->callback,$args);
    }

    public function attachEvent(Event &$event,$attachFunc){
        if(method_exists($event->target,$attachFunc)){
            $event->target->$attachFunc($event->name,$event->callback);
            return true;
        }else{
            return false;
        }
    }

    public function dispatchEvent($name){
        if(empty($this->_events[$name])){
            return true;
        }
        $callback = null;
        $events = & $this->_events[$name];
        if($events){
            $args = func_get_args();
            foreach($events as $i=>&$event){
                $args[0] = & $event;
                $callback = & $event->callback;
                if(!$event->persistent){
                    unset($events[$i]);
                }
                if(false === self::_executeEventCallback($callback,$args)){
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 执行事件回调
     * @param $callback
     * @param $args
     * @return bool|mixed
     */
    private static function _executeEventCallback(&$callback,$args){
        $return = true;
        if(is_string($callback) && strpos($callback,'::')!==false){
            $callback = explode('::',$callback);
        }
        if(is_array($callback)){
            if(is_object($callback[0])){
                list($classObj,$method) = $callback;
                $className = get_class($classObj);
            }else if(is_string($callback[0])){
                list($className,$method) = $callback;
                $classObj = null;
            }
            $reflectionMethod = new \ReflectionMethod($className,$method);
            try{
                $return = $reflectionMethod->invokeArgs($classObj,$args);
            }catch (\Exception $e){

            }
        }else if(is_string($callback)){
            $reflectionFunction = new \ReflectionFunction($callback);
            $return = $reflectionFunction->invokeArgs($args);
        }
        return $return;
    }

    public function forUnitTest(){
        print_r($this->_events);
    }
}