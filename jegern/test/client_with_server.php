<?php

global $client;
$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
$client->set(array(
    'socket_buffer_size' => 1024 * 1024 * 2,
));
$client->on("connect", function(swoole_client $cli) {
    echo posix_getpid()," client connect\n";
    $cli->send(posix_getpid()." client send\n");
});
$client->on("receive", function(swoole_client $cli, $data){
    echo posix_getpid()," client receive\ndata: $data\n";
});
$client->on("error", function(swoole_client $cli){
    echo posix_getpid()," client error\n";
});
$client->on("close", function(swoole_client $cli){
    echo posix_getpid()," client close\n";
});


$serv = new swoole_server('127.0.0.1', 9505);
$serv->set(array(
    'worker_num' => 4
));
$serv->on('workerStart',function(swoole_server $serv,$worker_id){
    echo "workerStart (id:{$worker_id} pid:".posix_getpid().")\n";
    if($worker_id==3){
        global $client;
        $client->connect('127.0.0.1', 9505);
    }
});
$serv->on('receive', function (swoole_server $serv, $fd, $from_id, $data) {
    echo $str = posix_getpid()." server receive\ndata: $data\n";
    $serv->send($fd,$str);
});
$serv->start();