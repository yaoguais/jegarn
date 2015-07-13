<?php

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
$client->connect('127.0.0.1', 9505);

echo "client 1 over\n";


$client2 = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
$client2->set(array(
    'socket_buffer_size' => 1024 * 1024 * 2,
));
$client2->on("connect", function(swoole_client $cli) {
    echo posix_getpid()," client 2 connect\n";
    $cli->send(posix_getpid()." client 2 send\n");
});
$client2->on("receive", function(swoole_client $cli, $data){
    echo posix_getpid()," client 2 receive\ndata: $data\n";
});
$client2->on("error", function(swoole_client $cli){
    echo posix_getpid()," client 2 error\n";
});
$client2->on("close", function(swoole_client $cli){
    echo posix_getpid()," client 2 close\n";
});
$client2->connect('127.0.0.1', 9505);

/*
/root/php5/bin/php multi-client.php
client 1 over
2978 client connect
2978 client 2 connect
2978 client receive
data: 2947 server receive
data: 2978 client send


2978 client 2 receive
data: 2948 server receive
data: 2978 client 2 send


/root/php5/bin/php single-server.php
2947 server receive
data: 2978 client send

2948 server receive
data: 2978 client 2 send
*/