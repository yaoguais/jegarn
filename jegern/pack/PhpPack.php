<?php

namespace jegern\pack;

abstract class PhpPack{
    public static function pack(&$data){
        return serialize($data);
    }
    public static function unpack(&$data){
        return unserialize($data);
    }
}