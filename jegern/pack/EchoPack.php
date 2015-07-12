<?php

namespace jegern\pack;

class EchoPack implements IPack{

    public function pack(&$data)
    {
        return $data;
    }

    public function unpack(&$data)
    {
        return $data;
    }
}