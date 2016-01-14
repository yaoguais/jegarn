<?php

use Yaf\Controller_Abstract;
use minions\http\ApiResponse;
use minions\base\Code;

class ErrorController extends Controller_Abstract{

    public function errorAction(\Exception $exception) {
        $exceptionCode = $exception->getCode();
        if($exceptionCode > 0){
            $responseObj = new ApiResponse($exceptionCode, $exception->getMessage());
        }else{
            $response = /*ini_get('yaf.environ') == 'product' ? null : */$exception->getMessage() . var_export(debug_backtrace(), true);
            $responseObj = new ApiResponse(Code::FAIL_INTERNAL_EXCEPTION, $response);
        }
        $responseObj->flush();
        return false;
    }
}