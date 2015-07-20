<?php

namespace jegern\app;

abstract class AppBase {

    abstract public function init();
    abstract public function toString();
    abstract public function parse($message);
}