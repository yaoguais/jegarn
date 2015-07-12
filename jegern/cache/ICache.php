<?php

namespace jegern\cache;

interface ICache {
    public function init($config);
    public function open();
    public function close();
    public function useDb($dbName);
    public function useTable($tableName);
    public function increase($key,$step=1);
    public function decrease($key,$step=1);
    public function set($key,$value);
    public function get($key);
    public function delete($key);
    public function setMap($key,$map);
    public function getMap($key,$fields=null);
    public function deleteMap($key);
    public function addToSet($key,$value);
    public function removeFromSet($key,$value);
    public function deleteSet($key);
    public function getSetSize($key);
    public function pushToList($key,$value);
    public function popFromList($key);
    public function getList($key,$start=0,$end=-1);
    public function getListSize($key);
    public function deleteList($key);
}