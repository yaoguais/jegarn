<?php

namespace jegarn\packet;

class ChatPacket extends Packet {

    protected $type = 'chat';

    public function getSubType() {
        return isset($this->content['type']) ? $this->content['type'] : null;
    }

    public function setSubType($value){
        if(!isset($this->content['type'])){
            $this->content['type'] = $value;
        }
    }
}