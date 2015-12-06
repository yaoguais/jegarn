<?php

namespace minions\yaf;
use minions\manager\UserManager;
use minions\request\ApiRequest;
use minions\response\ApiResponse;
use minions\response\Code;
use \Yaf\Controller_Abstract;
use \Yaf\Dispatcher;

class ApiControllerBase extends Controller_Abstract {

    protected $user;

    const OPTIONS = 'OPTIONS';
    const GET = 'GET';
    const HEAD = 'HEAD';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const TRACE = 'TRACE';
    const CONNECT = 'CONNECT';

    const TYPE_MAX = -1;
    const INT = -1;
    const LONG = -2;
    const STRING = -3;
    const FLOAT = -4;
    const DOUBLE = -5;
    const FILE  = -6;
    const UNSIGNED_INT = -7;
    //const BIG_INT = -8;

    public function init() {

        Dispatcher::getInstance()->disableView();
        $this->user = UserManager::getInstance()->getAuthorizedUser();
    }

    protected function setByUserInput(&$dst, $param){

        if(is_object($dst)){
            foreach($param as $k=>$v){
                if(is_string($k)){
                    if(property_exists($dst, $v)){
                        $dst->$v = ApiRequest::getParam($k);
                    }else{
                        return new ApiResponse(Code::FAIL_OBJECT_NO_THIS_PROPERTY, get_class($dst) . ' does not exits property ' . $k);
                    }
                }else{
                    if(property_exists($dst, $v)){
                        $dst->$v = ApiRequest::getParam($v);
                    }else{
                        return new ApiResponse(Code::FAIL_OBJECT_NO_THIS_PROPERTY, get_class($dst) . ' does not exits property ' . $v);
                    }
                }
            }
        }else{
            foreach($param as $k=>$v){
                if(is_string($k)){
                    $dst[$k] = ApiRequest::getParam($k, $v);
                }else{
                    $dst[$v] = ApiRequest::getParam($v);
                }
            }
        }

        return null;
    }

    protected function checkUserInput($param, $method){

        /* @var \Yaf\Request\Http $request */
        $request = Dispatcher::getInstance()->getRequest();
        if($method != null && $method != $request->getMethod()){
            return ApiResponse::newInstance(Code::FAIL_REQUEST_METHOD, $method.' support only');
        }
        if($param){
            foreach ($param as $k=>$v) {
                if(is_int($k) && $k <= self::TYPE_MAX){
                    switch($k){
                        case self::LONG:
                            if(!is_long($v)){
                                return ApiResponse::newInstance(Code::FAIL_PARAMETER_TYPE, $v . ' should be long');
                            }
                            break;
                        case self::INT:
                            if(!is_int($v)){
                                return ApiResponse::newInstance(Code::FAIL_PARAMETER_TYPE, $v . ' should be integer');
                            }
                        case self::UNSIGNED_INT:
                            if($v<0){
                                return ApiResponse::newInstance(Code::FAIL_PARAMETER_TYPE, $v . ' should be unsigned integer');
                            }
                            break;
                        case self::STRING:
                            if(!is_string($v)){
                                return ApiResponse::newInstance(Code::FAIL_PARAMETER_TYPE, $v . ' should be string');
                            }
                            break;
                        case self::FLOAT:
                            if(!is_float($v)){
                                return ApiResponse::newInstance(Code::FAIL_PARAMETER_TYPE, $v . ' should be float');
                            }
                            break;
                        case self::DOUBLE:
                            if(!is_double($v)){
                                return ApiResponse::newInstance(Code::FAIL_PARAMETER_TYPE, $v . ' should be float');
                            }
                            break;
                        case self::FILE:
                            if($request->getFiles($v) === null){
                                return ApiResponse::newInstance(Code::FAIL_PARAMETER_TYPE, $v . ' should be file');
                            }
                            break;
                        default:
                            return ApiResponse::newInstance(Code::FAIL_PARAMETER_TYPE, 'type of ' . $v . ' not support');
                    }
                }else{
                    if(ApiRequest::getParam($v) === null){
                        return ApiResponse::newInstance(Code::FAIL_PARAMETER_MISSING, 'parameter ' . $v . ' missing');
                    }
                }
            }
        }

        return null;
    }
}