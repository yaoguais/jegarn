<?php

namespace jegarn\server;

abstract class BufferProcessor {

    protected $idBufferMap;

    abstract public function init($id);
    abstract public function append($id, $buffer);
    abstract public function reset($id);
    abstract public function destroy($id);
    abstract public function consumePacket($id, &$return = null);
}