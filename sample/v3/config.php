<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-26
 * Time: 下午9:08
 */

return [
    'server' => [
        'class' => 'minions\\server\\SwooleServer',
        'host' => '0.0.0.0',
        'port' => 9501,
        'mode' => SWOOLE_PROCESS,
        'sock_type' => SWOOLE_SOCK_TCP,
        'worker_num' => 4,
        'open_eof_check' => true,
        'package_eof' => "\r\n"
    ]
];