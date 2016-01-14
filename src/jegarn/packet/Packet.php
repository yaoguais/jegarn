<?php

namespace jegarn\packet;

class Packet {

    protected $from;
    protected $to;
    protected $type;
    protected $content;

    public function getFrom() {
        return $this->from;
    }

    public function setFrom($from) {
        $this->from = $from;
    }

    public function getTo() {
        return $this->to;
    }

    public function setTo($to) {
        $this->to = $to;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type){
        if($this->type === null){
            $this->type = $type;
        }
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function isFromSystemUser(){
        return 'system' === $this->from;
    }

    public function setFromSystemUser(){
        $this->from = 'system';
    }

    public function setPacket(Packet $packet){
        $this->setFrom($packet->getFrom());
        $this->setTo($packet->getTo());
        $this->setType($packet->getType());
        $this->setContent($packet->getContent());
    }

    public function convertToArray(){
        return ['from' => $this->getFrom(), 'to' => $this->getTo(), 'type' => $this->getType(), 'content' => $this->getContent()];
    }

    public function getReversedPacket(){
        $packet = clone $this;
        $packet->setFrom($this->getTo());
        $packet->setTo($this->getFrom());
        return $packet;
    }

    /**
     * @param \jegarn\packet\Packet $packet
     * @return null|static
     */
    public static function getPacketFromPacket(Packet $packet){
        if($packet && $packet->getType()){
            $self = new static();
            if($packet->getType() == $self->getType()){
                $self->setPacket($packet);
                return $self;
            }
        }
        return null;
    }

    public static function getPacketFromArray($arr){
        if(isset($arr['from'], $arr['to'], $arr['type'], $arr['content'])){
            $packet = new Packet();
            $packet->setFrom($arr['from']);
            $packet->setTo($arr['to']);
            $packet->setType($arr['type']);
            $packet->setContent($arr['content']);
            return $packet;
        }else{
            return false;
        }
    }
}