<?php

namespace jegern\pack;

class GluePack implements IPack{

    protected $glue = ",";

    public function __construct($glue=","){
        $this->glue = $glue;
    }

    public function pack(&$data)
    {
        return implode($this->glue,$data);
    }

    public function unpack(&$data)
    {
        return explode($this->glue,$data);
    }
}