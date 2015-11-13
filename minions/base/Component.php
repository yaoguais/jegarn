<?php

namespace minions\base;
use \Exception;

class Component {

    protected $listeners;
    protected $events;

    public function isListenerExists($listener){

        if( !$listener || !( $listener instanceof Listener ) ){
            throw new Exception('listener must instance of \\minions\\base\\Listener');
        }

        return isset( $this->listeners[get_class($listener)] );
    }

    public function addListener($listener){

        if(!$this->isListenerExists($listener)){
            $this->listeners[get_class($listener)] = $listener;
        }
    }

    public function removeListener($listener){

        if($this->isListenerExists($listener)){
            unset($this->listeners[get_class($listener)]);
        }
    }

    public function __call($name, $arguments){

        if(!method_exists($this,$name)){
            throw new Exception('method not exits');
        }
        if($this->events && isset($this->events[$name])){
            if($this->listeners){
                foreach($this->listeners as $listener){
                    $callback = 'before'.ucwords($name);
                    if((null !== ($ret = call_user_func([$listener,$callback],$arguments))) || $ret !== Code::SUCCESS){
                        return $ret;
                    }
                }
            }
            $ret = call_user_func([$this,$name],$arguments);
            if($this->listeners){
                $arguments[] = $ret;
                foreach($this->listeners as $listener){
                    $callback = 'after'.ucwords($name);
                    if((null !== ($ret = call_user_func([$listener,$callback],$arguments))) || $ret !== Code::SUCCESS){
                        return $ret;
                    }
                }
            }
        }else{
            $ret = call_user_func([$this,$name],$arguments);
        }

        return $ret;
    }
}