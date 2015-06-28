<?php

require(__DIR__.'/bootstrap.php');
$config = require(__DIR__.'/config.php');
\minions\manager\ConfigurationManager::getInstance()->init($config);
\minions\server\ServerManager::getInstance()->startServers();
