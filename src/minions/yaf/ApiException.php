<?php

namespace minions\yaf;

use minions\response\ApiResponse;

class ApiException extends \Exception {

    public function __construct($message = '', $code = 0, \Exception $previous = null) {

        parent::__construct($message, $code, $previous);
    }
}