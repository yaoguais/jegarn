<?php

namespace jegarn\log;

class FileLoggerHandler implements LoggerHandler{

    protected $levelFileInfo;

    public function __construct($levelFileInfo){
        $this->levelFileInfo = $levelFileInfo;
    }

    public function addRecord($level, $message, $context = []){
        if(isset($this->levelFileInfo[$level]) && isset(Logger::$LEVELS[$level]) && $this->levelFileInfo[$level]){
            $levelName = Logger::$LEVELS[$level];
            $date = date('r', time());
            file_put_contents($this->levelFileInfo[$level], "[{$levelName}] {$date} $message\n", FILE_APPEND);
        }
    }
}