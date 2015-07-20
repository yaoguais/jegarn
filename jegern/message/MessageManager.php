<?php

namespace jegern\message;
use jegern\app\AppManager;
use jegern\server\UserManager;

abstract class MessageManager {

    const ERROR_BODY_STRING_TO_SHORT = 0;
    const ERROR_BODY_PACK = 1;
    const ERROR_APP_ID = 2;
    const ERROR_MODULE_ID = 3;
    const ERROR_ID_STRING = 4;
    const ERROR_ID_PACK = 5;
    const ERROR_BODY_TOO_SHORT = 6;

    public static $error;

    public static function dispatchMessage(\swoole_server $server, $fd, $from_id, $message){

        $point  = 0;
        $maxLength = strlen($message) - 2;
        while($point<$maxLength){
            self::$error = [];
            $startPoint = $point;
            $bodyLengthString = substr($message,$point,2);
            $point += 2;
            if(strlen($bodyLengthString) < 2){
                self::$error[] = self::ERROR_BODY_STRING_TO_SHORT;
                break;
            }
            $bodyLengthArray = unpack('n',$bodyLengthString);
            if(empty($bodyLengthArray)){
                self::$error[] = self::ERROR_BODY_PACK;
                break;
            }
            if($point+1+1+4+$bodyLengthArray[1]>=$maxLength){
                self::$error[] = self::ERROR_BODY_TOO_SHORT;
                break;
            }
            $appId = substr($message,$point,1);
            $point += 1;
            if(strlen($appId)<1){
                self::$error[] = self::ERROR_APP_ID;
                break;
            }
            $moduleId = substr($message,$point,1);
            $point += 1;
            if(strlen($moduleId) < 1){
                self::$error = self::ERROR_MODULE_ID;
                break;
            }
            $idString = substr($message,$point,4);
            $point += 4;
            if(strlen($idString) < 4){
                self::$error[] = self::ERROR_ID_STRING;
                break;
            }
            $idStringArray = unpack('N',$idString);
            if(empty($idStringArray)){
                self::$error[] = self::ERROR_ID_PACK;
                break;
            }
            if(empty(self::$error)){
                $point += $bodyLengthArray[1];
                self::dispatch($server,$fd,$from_id,$appId,$moduleId,$idStringArray[1],substr($message,$startPoint,2+1+1+4+$bodyLengthArray[1]));
            }else{
                self::error($server,$fd,$from_id,self::$error,substr($message,$startPoint,2+1+1+4+$bodyLengthArray[1]));
                break;
            }
        }

    }


    protected static function dispatch(\swoole_server $server, $fd, $from_id, $appId,$moduleId,$id,$message){

        if($appId == AppManager::APP_AUTHOR) {

        }else if(!UserManager::hasUser($fd)){

        }

    }

    protected static function error(\swoole_server $server, $fd, $from_id,$error,$message){

    }

    public function sendMessage(\swoole_server $server, $fd, $from_id,$uid,$message){

    }
}