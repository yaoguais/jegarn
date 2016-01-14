<?php
require __DIR__ . '/../../src/bootstrap.php';
$config = require __DIR__ . '/../../examples/web-chat-system/config/server.php';
\jegarn\cache\Cache::getInstance()->initConfig($config['cache']);