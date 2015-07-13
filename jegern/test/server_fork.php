<?php

if($argc < 3){
    exit("php server_fork.php host port host2 port2\n");
}

$host = $argv[1];
$port = $argv[2];

if(isset($argv[3])){

    $host2 = $argv[3];
    $port2 = $argv[4];

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
    $client->connect($host2, $port2);
}






$serv = new swoole_server($host, $port);
$serv->set(array(
    'worker_num' => 4
));
$serv->on('receive', function (swoole_server $serv, $fd, $from_id, $data) {
    echo $str = posix_getpid()." server receive\ndata: $data\n";
    $serv->send($fd,$str);
});
$serv->start();
echo "start over\n";

/*
首先 /root/php5/bin/php server_fork.php 127.0.0.1 9501 建立服务器
然后 /root/php5/bin/php server_fork.php 127.0.0.1 9502 127.0.0.1 9501
使$client连接上个服务器,然后发送数据。这时$client处于4个进程中，收到消息应该是4个进程都应该收到。
/root/php5/bin/php server_fork.php 127.0.0.1 9501
4839 server receive
data: 4853 client send

/root/php5/bin/php server_fork.php 127.0.0.1 9502 127.0.0.1 9501
PHP Fatal error:  swoole_server::__construct(): eventLoop has been created. Unable to create swoole_server. in /home/yaoguai/github/minions/jegern/test/server_fork.php on line 42
4853 client connect
4853 client receive
data: 4839 server receive
data: 4853 client send
测试结论：
*/