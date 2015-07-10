<?php

namespace jegern\cache;

class RedisCache implements ICache{

    protected static $caches;

    public function increase($key, $step = 1) {

    }

    public function decrease($key, $step = 1) {

    }

    public function useDb($dbName) {

    }

    public function useTable($tableName) {

    }

    public function set($key, $value) {

    }

    public function get($ley, &$value) {

    }

    public function setMap($key, $map) {

    }

    public function getMap($key, &$map) {

    }

    public function addToSet($key, $value) {

    }

    public function removeFromSet($key, &$value) {

    }

    public function pushToList($key, $value) {

    }

    public function popFromList($key, &$value) {

    }

    public function getList($key, $start, $end = -1) {

    }
}