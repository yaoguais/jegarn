<?php

use jegarn\cache\Cache;
use minions\db\Db;

define('PIC_HOST', '/upload/');
define('TEST_HOST', 'http://192.168.199.243');
require __DIR__ . '/../../examples/web-chat-system/src/bootstrap.php';
require __DIR__ . '/../../sdk/php/src/jegarn.php';
require __DIR__ . '/AppTestBase.php';

$config = new \Yaf\Config\Ini(__DIR__ . '/../../examples/web-chat-system/config/application.ini','develop');
Db::getInstance()->initConfig($config->get('application')->get('database'));
Cache::getInstance()->initConfig($config->get('application')->get('cache'));