<?php

namespace minions\response;

class ApiResponse {

    public $code;
    public $response;
    protected static $instances;
    protected static $return = false;

    public static function newInstance($code, $response) {

        $instance = new static();
        $instance->code = $code;
        $instance->response = $response;
        self::$instances[] = $instance;
        return $instance;
    }

    public static function response(){

        if(self::$return){
            echo json_encode(['code' => Code::FAIL_INTERNAL_MULTI_SEND, 'response' => null], JSON_UNESCAPED_UNICODE);
        }else if(!self::$instances){
            echo json_encode(['code' => Code::FAIL_INTERNAL_NO_RESPONSE, 'response' => null], JSON_UNESCAPED_UNICODE);
        }else if(count(self::$instances) > 1){
            echo json_encode(['code' => Code::FAIL_INTERNAL_MULTI_RESPONSE, 'response' => self::$instances], JSON_UNESCAPED_UNICODE);
        }else{
            $instance = self::$instances[0];
            if($instance->code === null){
                echo json_encode(['code' => Code::FAIL_INTERNAL_NO_CODE, 'response' => null], JSON_UNESCAPED_UNICODE);
            }else{
                echo json_encode(['code' => $instance->code, 'response' => $instance->response], JSON_UNESCAPED_UNICODE);
            }
        }
        self::$return = true;
    }
}