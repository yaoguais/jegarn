<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 下午8:58
 */
define('MINIONS_ROOT',realpath(__DIR__.'/'));

if(!function_exists('minions_autoload')){
    function minions_autoload($class){
        static $_classes;
        if(!isset($_classes[$class])){
            $_classes[$class] = true;
            include(MINIONS_ROOT.'/'.strtr(substr($class,8),'\\','/').'.php');
        }
    }
    spl_autoload_register('minions_autoload');
}
