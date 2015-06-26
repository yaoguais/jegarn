<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 下午9:20
 */

require(__DIR__.'/minions/bootstrap.php');
$config = require(__DIR__.'/config/config.php');
$configManager = minions\config\ConfigManager::getInstance();
$configManager->init($config);
$configManager::$server->start();