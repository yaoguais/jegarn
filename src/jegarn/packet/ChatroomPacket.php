<?php

namespace jegarn\packet;

class ChatroomPacket extends Packet {

    protected $type = 'chatroom';

    public function __construct(){
        $this->to = 'all';
    }

    public function getSubType() {
        return isset($this->content['type']) ? $this->content['type'] : null;
    }

    public function setSubType($value){
        if(!isset($this->content['type'])){
            $this->content['type'] = $value;
        }
    }

    public function getGroupId(){
        return isset($this->content['group_id']) ? $this->content['group_id'] : null;
    }

    public function setGroupId($value){
        $this->content['group_id'] = $value;
    }
}