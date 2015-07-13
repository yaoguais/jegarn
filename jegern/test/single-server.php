<?php

$serv = new swoole_server('127.0.0.1', 9505);
$serv->set(array(
    'worker_num' => 4
));
$serv->on('receive', function (swoole_server $serv, $fd, $from_id, $data) {
    echo $str = posix_getpid()." server receive\ndata: $data\n";
    $serv->send($fd,$str);
});
$serv->start();