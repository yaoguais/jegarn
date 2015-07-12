<?php

require __DIR__.'/../bootstrap.php';

$redis = new jegern\cache\RedisCache();

$redis->init([
    'host' => '127.0.0.1',
    'port' => 6379,
    'timeout' => 0
]);

if(!$redis->open()){
    echo "open error\n";
}

$key = 'foo';
$val = 'bar';

if(!$redis->delete($key)){
    echo "delete error first\n";
}

if(!$redis->set($key,$val)){
    echo "set error\n";
}

if($val != $redis->get($key)){
    echo "get error \n";
}

if(!$redis->delete($key)){
    echo "delete error get\n";
}

if(10 != $redis->increase($key,10)){
    echo "increase error \n";
}

if(5 != $redis->decrease($key,5)){
    echo "decrease error\n";
}

if(!$redis->delete($key)){
    echo "delete error increase\n";
}

$map = [
    'uid' => '1',
    'username' => 'admin',
    'password' => 'admin1'
];

if(!$redis->setMap($key,$map)){
    echo "set map error\n";
}

$result = array_diff($map,$ret = $redis->getMap($key));
if(!empty($result)){
    print_r($ret);
    echo "get map error 1\n";
}

$result = $redis->getMap($key,['username']);
if(!isset($result['username']) || $result['username']!='admin'){
    print_r($result);
    echo "get map error 2\n";
}

if(!$redis->deleteMap($key)){
    echo "delete error map\n";
}

if(!$redis->addToSet($key,2) || !$redis->addToSet($key,3) || !$redis->addToSet($key,4)){
    echo "add set error\n";
}

if(3 != $redis->getSetSize($key)){
    echo "set size error\n";
}

if(1 != $redis->removeFromSet($key,4)){
    echo "set remove error\n";
}

$result = array_diff(['2','3'],$ret = $redis->getSet($key));
if(!empty($result)){
    print_r($ret);
    echo "get set error\n";
}

if(!$redis->deleteSet($key)){
    echo "delete error set\n";
}

if(!$redis->pushToList($key,10) || !$redis->pushToList($key,11) || !$redis->pushToList($key,12) || !$redis->pushToList($key,13)){
    echo "push list error";
}

if(4 != $redis->getListSize($key)){
    echo "get list size error\n";
}

if(10 != $redis->popFromList($key)){
    echo "list pop error \n";
}

$result = array_diff(['11','12','13'],$ret = $redis->getList($key));
if(!empty($result)){
    print_r($ret);
    echo "get list error\n";
}

if(!$redis->deleteList($key)){
    echo "delete list error\n";
}