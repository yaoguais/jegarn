<?php

require __DIR__.'/model_init.php';

$object = new jegern\model\UserConnectionModel();

var_dump($object->addUser(1,'127.0.0.1',9505,6,13));

var_dump($object->getUser(1));

var_dump($object->removeUser(1));

var_dump($object->getUser(1));

