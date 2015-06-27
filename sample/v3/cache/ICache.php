<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-26
 * Time: 下午8:40
 */

namespace minions\cache;

interface ICache{
    public function init($config);
    public function open();
    public function set($key,$value);
    public function get($key);
    public function close();
}