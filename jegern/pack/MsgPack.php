<?php

namespace jegern\pack;

class MsgPack implements IPack{
    public function pack(&$data){
        return msgpack_pack($data);
    }
    public function unpack(&$data){
        return msgpack_unpack($data);
    }
}