<?php

namespace jegarn\server;

abstract class HeadNServer extends Server {

    protected $readBuffer;

    public function onMessage($fd, $message){

    }
}