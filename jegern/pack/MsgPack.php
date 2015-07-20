<?php

namespace jegern\pack;

abstract class MsgPack {
    public static function pack(&$data){
        return msgpack_pack($data);
    }
    public static function unpack(&$data){
        return msgpack_unpack($data);
    }
}