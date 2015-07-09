<?php

namespace jegern\pack;

class PhpPack implements IPack{
    public function pack(&$data){
        return serialize($data);
    }
    public function unpack(&$data){
        return unserialize($data);
    }
}