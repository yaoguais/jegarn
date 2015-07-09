<?php

namespace jegern\pack;

class GluePack implements IPack{

    public function pack(&$data)
    {
        return implode("\n",$data);
    }

    public function unpack(&$data)
    {
        return explode("\n",$data);
    }
}