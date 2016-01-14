<?php

use \minions\http\ApiControllerBase;
use \minions\http\ApiResponse;
use \minions\base\Code;

class IndexController extends ApiControllerBase {

    public function indexAction(){
        // forward to admin module to login
        (new ApiResponse(Code::FAIL_ACTION_NOT_REACHABLE, null))->flush();
        return false;
    }
}