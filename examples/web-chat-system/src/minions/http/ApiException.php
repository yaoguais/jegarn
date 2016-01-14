<?php

namespace minions\http;
use Exception;

class ApiException extends Exception {

    public function __construct($message = '', $code = 0, Exception $previous = null) {

        parent::__construct($message, $code, $previous);
    }
}