<?php

use \minions\yaf\ApiControllerBase;
use \minions\response\ApiResponse;
use \minions\response\Code;

class IndexController extends ApiControllerBase {

    public function indexAction(){

        // forward to admin module to login
        return ApiResponse::newInstance(Code::FAIL_ACTION_NOT_REACHABLE);
    }
}