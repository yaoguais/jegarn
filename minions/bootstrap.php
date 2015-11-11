<?php

if(!defined('MINIONS_ROOT')){
    define('MINIONS_ROOT', realpath(__DIR__ . '/../'));
    spl_autoload_register(function($class){
        $file =  MINIONS_ROOT. '/' .str_replace('\\', '/', $class) . '.php';
        if(file_exists($file)){
            require_once $file;
        }
    });
}

