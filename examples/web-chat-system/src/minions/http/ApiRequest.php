<?php

namespace minions\http;

class ApiRequest{

    public static function getRemoteIp(){

        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    public static function getParam($key, $default = null){
        // i don't need client push js code, so encode all for prevent xss
        return isset($_POST[$key]) ? htmlspecialchars($_POST[$key]) : (isset($_GET[$key]) ? htmlspecialchars($_GET[$key]) : $default);
    }
}