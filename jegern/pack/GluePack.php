<?php

namespace jegern\pack;

abstract class GluePack{

    public static $glue = "\n";

    public static function pack(&$data)
    {
        return implode(self::$glue,$data);
    }

    public static function unpack(&$data)
    {
        return explode(self::$glue,$data);
    }
}