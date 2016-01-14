<?php

namespace jegarn\manager;
use jegarn\packet\Packet;
use jegarn\session\Session;
use jegarn\listener\PacketInterface;
use jegarn\log\Logger;

class PacketManager extends BaseManager {

    protected $listeners;

    public static function getInstance($class = __CLASS__){
        return parent::getInstance($class);
    }

    public function dispatchReceive(Packet $packet, Session $session){

        if($this->listeners){
            /* @var PacketInterface $listener */
            foreach($this->listeners as $listener){
                Logger::addInfo('packet dispatch receive('.get_class($listener).')');
                if(false === $listener->dispatchReceivePacket(clone $packet, $session)){
                    return false;
                }
            }
        }
    }

    public function dispatchSend(Packet $packet, Session $session){
        if($this->listeners){
            /* @var PacketInterface $listener */
            foreach($this->listeners as $listener){
                Logger::addInfo('packet dispatch send('.get_class($listener).')');
                if(false === $listener->dispatchSendPacket(clone $packet, $session)){
                    return false;
                }
            }
        }
    }

    public function dispatchOffline(Session $session){
        if($this->listeners){
            /* @var PacketInterface $listener */
            foreach($this->listeners as $listener){
                Logger::addInfo('packet dispatch offline('.get_class($listener).')');
                if(false === $listener->dispatchOfflinePacket($session)){
                    return false;
                }
            }
        }
    }

    public function addListener(PacketInterface $listener){
        if($this->hasListener($listener)){
            $this->removeListener($listener);
        }
        $this->listeners[] = $listener;
    }

    public function removeListener(PacketInterface $listener){
        if($this->listeners){
            foreach($this->listeners as $k => &$l){
                if($listener === $l){
                    unset($this->listeners[$k]);
                }
            }
        }
    }

    public function hasListener(PacketInterface $listener){
        if($this->listeners){
            foreach($this->listeners as &$l){
                if($listener === $l){
                    return true;
                }
            }
        }
        return false;
    }
}