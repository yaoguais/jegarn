<?php

namespace minions\listener;
use minions\base\Listener;

class TestListener extends Listener {

    public function beforeTest($arg){

        echo "before test $arg \n";
    }

    public function afterTest($arg,$ret){

        echo "after test $arg $ret \n";
    }
}