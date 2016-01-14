<?php

namespace jegarn\log;

interface LoggerHandler{

    public function addRecord($level, $message, $context = []);
}