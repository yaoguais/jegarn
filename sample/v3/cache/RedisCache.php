<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-26
 * Time: 下午8:39
 */

namespace minions\cache;

final class RedisCache implements ICache{
    public function init($config){}
    public function open(){}
    public function set($key,$value){}
    public function get($key){}
    public function close(){}
}