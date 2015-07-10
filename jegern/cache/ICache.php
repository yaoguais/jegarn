<?php

namespace jegern\cache;

interface ICache {

    const ERROR = -1;
    const SUCCESS = 1;

    /**
     * 初始化的回调
     * @param $config
     * @return mixed
     */
    //public function init($config);

    /**
     * 改变数据库时触发
     * @param $dbName
     * @return mixed
     */
    public function useDb($dbName);

    /**
     * 改变表或集合等时触发
     * @param $tableName
     * @return mixed
     */
    public function useTable($tableName);

    /**
     * 自增并获取自增后的值
     * @param $key
     * @param int $step
     * @return int 增加后的值
     */
    public function increase($key,$step=1);

    /**
     * 自减并获取自减后的值
     * @param $key
     * @param int $step
     * @return int 减少后的值
     */
    public function decrease($key,$step=1);

    /**
     * @param $key
     * @param $value
     * @return int status
     */
    public function set($key,$value);

    /**
     * @param $ley
     * @param $value
     * @return int status
     */
    public function get($ley,&$value);

    /**
     * 设置Map
     * @param $key
     * @param array $map
     * @return int status
     */
    public function setMap($key,$map);

    /**
     * @param $key
     * @param $map //成功后自动填充
     * @return int status
     */
    public function getMap($key,&$map);

    /**
     * @param $key
     * @param $value
     * @return int status
     */
    public function addToSet($key,$value);

    /**
     * @param $key
     * @param $value //成功后自动填充原先的值
     * @return int status
     */
    public function removeFromSet($key,&$value);

    /**
     * @param $key
     * @param $value
     * @return int status
     */
    public function pushToList($key,$value);

    /**
     * @param $key
     * @param $value //成功后自动填充
     * @return int status
     */
    public function popFromList($key,&$value);

    /**
     * @param $key
     * @param $start
     * @param $end -1 for all
     * @return mixed failed:null success:array
     */
    public function getList($key,$start,$end=-1);
}