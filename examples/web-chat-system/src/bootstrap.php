<?php
if(!defined('MINIONS_ROOT')){
    define('MINIONS_ROOT', __DIR__);
    spl_autoload_register(function($class){
        if(substr($class,0,7) === 'minions'){
            $file =  MINIONS_ROOT. '/' .str_replace('\\', '/', $class) . '.php';
            if(file_exists($file)){
                require_once $file;
            }
        }
    });
}