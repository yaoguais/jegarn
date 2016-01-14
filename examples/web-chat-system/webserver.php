<?php

use jegarn\cache\Cache;
use jegarn\log\FileLoggerHandler;
use jegarn\log\Logger;
use jegarn\manager\ServerManager;
use jegarn\manager\PacketManager;
use jegarn\server\SwooleWebServer;

require __DIR__ . '/../../src/bootstrap.php';
$config = require __DIR__ . '/config/webserver.php';
isset($argv[1]) && ($config['server']['host'] = $argv[1]);
isset($argv[2]) && ($config['server']['port'] = $argv[2]);
Cache::getInstance()->initConfig($config['cache']);
Logger::addHandler(new FileLoggerHandler($config['file_logger']));
foreach($config['listener'] as $listener) PacketManager::getInstance()->addListener(new $listener);
ServerManager::getInstance()->addServer(new SwooleWebServer())->initConfig($config['server'])->start();