<?php

namespace jegarn\packet;

class NotificationPacket extends Packet {

    protected $type = 'notification';

    public function getSubType() {
        return isset($this->content['type']) ? $this->content['type'] : null;
    }

    public function setSubType($value){
        if(!isset($this->content['type'])){
            $this->content['type'] = $value;
        }
    }
}