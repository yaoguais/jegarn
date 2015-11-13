<?php

use \minions\manager\UserManager;
use \minions\listener\TestListener;

require_once __DIR__ . '/../minions/bootstrap.php';

$testListener = new TestListener();

$userManager = UserManager::getInstance();
$userManager->addListener($testListener);
$userManager->test();