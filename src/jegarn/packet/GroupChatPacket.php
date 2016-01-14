<?php

namespace jegarn\packet;

class GroupChatPacket extends Packet {

    protected $type = 'groupchat';

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

    public function isSendToAll(){
        return $this->getTo() == 'all';
    }

    public function setSendToAll(){
        $this->to = 'all';
    }
}