<?php

require __DIR__.'/../bootstrap.php';

$redis = new jegern\cache\RedisCache();
$redis->init([
    'host' => '127.0.0.1',
    'port' => 6379,
    'timeout' => 0
]);

jegern\cache\CacheManager::addCache('user',$redis);
jegern\cache\CacheManager::addCache('configuration',$redis);
jegern\cache\CacheManager::addCache('user_connection',$redis);
