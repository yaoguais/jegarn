<?php

define('JEGERN_ROOT',__DIR__);
if(!function_exists('jegern_autoload')){
    function jegern_autoload($class){
        static $_classes;
        if(!isset($_classes[$class])){
            $_classes[$class] = true;
            $file = JEGERN_ROOT.str_replace('\\','/',substr($class,6)).'.php';
            if(file_exists($file)){
                include $file;
            }
        }
    }
    spl_autoload_register('jegern_autoload');
}
