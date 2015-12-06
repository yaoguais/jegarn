<?php

define('TEST_HOST', 'http://192.168.199.243');
require __DIR__ . '/AppTestBase.php';
require __DIR__ . '/../../src/bootstrap.php';

$config = new \Yaf\Config\Ini(__DIR__ . '/../../config/application.ini','develop');
\minions\manager\DbManager::getInstance()->initConfig($config->get('application')->get('database'));