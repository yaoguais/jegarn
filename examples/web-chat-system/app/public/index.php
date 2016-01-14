<?php

define('UPLOAD_PATH', __DIR__ . '/upload');
define('PIC_HOST', '/upload/');
define('APPLICATION_PATH', substr(__FILE__, 0, -17));
$application = new \Yaf\Application(APPLICATION_PATH . "/../config/application.ini");
$application->bootstrap()->run();