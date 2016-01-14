<?php

namespace minions\http;

use minions\base\Response;
use minions\base\Code;

class ApiResponse extends Response {

    public $code;
    public $response;

    public function __construct($code, $response) {
        $this->code = $code;
        $this->response = $response;
    }

    public function flush() {
        header('Content-Type:application/json;Charset=UTF-8');
        echo json_encode(['code' => null === $this->code ? Code::FAIL_INTERNAL_NO_RESPONSE : $this->code, 'response' => $this->response], JSON_UNESCAPED_UNICODE);
    }
}