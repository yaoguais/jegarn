<?php

namespace jegarn\server;

class SwooleHeaderNBufferProcessor extends HeaderNBufferProcessor{

    public function append($id, $buffer){
        $this->idBufferMap[$id] = $buffer;
    }

    public function reset($id){
        $this->idBufferMap[$id] = '';
    }

    public function destroy($id){
        unset($this->idBufferMap[$id]);
    }

    public function consumePacket($id, &$return = null){
        return substr($this->idBufferMap[$id], $this->headerSize);
    }

    public function init($id){
        return $this->idBufferMap[$id];
    }
}