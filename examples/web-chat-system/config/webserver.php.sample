<?php

use jegarn\log\Logger;

return [
    'cache'       => [
        'host'     => '192.168.199.242',
        'port'     => 6379,
        'timeout'  => 0.0,
        'password' => null
    ],
    'server'      => [
        'host'          => '192.168.199.243',
        'port'          => 9501,
        'worker_num'    => 4,
        'dispatch_mode' => 2,
        'log_file'      => __DIR__ . '/../logs/webserver_swoole.log',
        //'daemonize'     => 1,
        'ssl_cert_file' => __DIR__ . '/ssl.crt',
        'ssl_key_file' => __DIR__ . '/ssl.key'
    ],
    'listener' => [
        'jegarn\listener\AuthPacketListener', // always add to first, not authorized user would do nothing
        'jegarn\listener\NotificationPacketListener',
        'jegarn\listener\ChatPacketListener',
        'jegarn\listener\GroupChatPacketListener',
        'jegarn\listener\ChatroomPacketListener'
    ],
    'file_logger' => [
        Logger::DEBUG     => __DIR__ . '/../logs/webserver_debug.log',
        Logger::INFO      => __DIR__ . '/../logs/webserver_info.log',
        Logger::NOTICE    => __DIR__ . '/../logs/webserver_notice.log',
        Logger::WARNING   => __DIR__ . '/../logs/webserver_warning.log',
        Logger::ERROR     => __DIR__ . '/../logs/webserver_error.log',
        Logger::CRITICAL  => __DIR__ . '/../logs/webserver_critical.log',
        Logger::ALERT     => __DIR__ . '/../logs/webserver_alter.log',
        Logger::EMERGENCY => __DIR__ . '/../logs/webserver_emergency.log',
    ]
];