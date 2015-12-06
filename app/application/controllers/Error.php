<?php

use \Yaf\Controller_Abstract;
use \minions\response\ApiResponse;
use \minions\response\Code;
use \minions\yaf\ApiException;

class ErrorController extends Controller_Abstract{

    public function errorAction(\Exception $exception){

        if($exception instanceof Yaf\Exception\LoadFailed\View || $exception instanceof ApiException){
            ApiResponse::response();
        }else{
            ApiResponse::newInstance(Code::FAIL_INTERNAL_EXCEPTION, $exception->getMessage())->response();
        }

        return false;
    }
}