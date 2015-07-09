<?php

namespace jegern\pack;

interface IPack{
    /**
     * @param $data
     * @return string
     */
    public function pack(&$data);
    /**
     * @param string $data
     * @return mixed
     */
    public function unpack(&$data);
}