<?php

if(!defined('JEGARN_ROOT')){
    define('JEGARN_ROOT', __DIR__);
    spl_autoload_register(function($class){
        if(substr($class,0,6) === 'jegarn'){
            $file =  JEGARN_ROOT. '/' .str_replace('\\', '/', $class) . '.php';
            if(file_exists($file)){
                require_once $file;
            }
        }
    });

}