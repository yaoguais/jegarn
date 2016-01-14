<?php

namespace minions\http;

class ApiRequest{

    public static function getRemoteIp(){

        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    public static function getParam($key, $default = null){

        return isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $default);
    }
}