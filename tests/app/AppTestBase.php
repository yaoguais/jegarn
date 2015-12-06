<?php

use minions\response\Code;
use minions\util\Curl;


abstract class AppTestBase extends PHPUnit_Framework_TestCase {

    const IMAGE = 'image.jpg';
    const VOICE = 'voice.mp3';
    const ARCHIVE = 'archive.zip';

    protected function getFile($file){

        return __DIR__ . '/files/'.$file;
    }

    protected function request($pathInfo, $params = [], $isPost = false, $files = null) {

        $url = (defined('TEST_HOST') ? TEST_HOST : '') . '/' . trim($pathInfo, '/');
        $opts            = [ CURLOPT_RETURNTRANSFER => true,
                             CURLOPT_HEADER         => false,
                             CURLOPT_SSL_VERIFYPEER => false,
                             CURLOPT_SSL_VERIFYHOST => false ];
        if( $isPost ){
            if(is_array($files) && is_array($params)){
                $opts = Curl::curlCustomPostFields($params,$files) + $opts;
            }else{
                $opts[CURLOPT_POST]       = true;
                $opts[CURLOPT_POSTFIELDS] = is_array($params) ? http_build_query($params) : $params;
            }
        }else{
            $url .= '?' . http_build_query($params);
        }

        $ret = &Curl::runCurl($url, $opts, $info);
        $logFile = str_replace('/','_',trim($pathInfo,'/')) . '.log';
        if(!$isPost){
            $this->log($logFile, 'GET', $url);
        }else{
            $this->log($logFile, 'POST', $url."\n".var_export($params,true).($files ? var_export($files,true) : ''));
        }
        if( ( false !== $ret ) && ( 200 == $info['http_code'] ) ){
            $resp = json_decode($ret, true);
            $this->log($logFile, 'SUCCESS', $ret."\ndecode:\n".($resp ? var_export($resp, true) : $resp));
            return $resp;
        }else{
            $this->log($logFile, 'ERROR', var_export($ret,true)."info:\n".var_export($info,true));
            return null;
        }
    }

    protected function log($file, $tag, $data){

        file_put_contents(__DIR__ . '/logs/' . $file, '['.$tag.']'.date('Y-m-d H:i:s')."\n".var_export($data, true)."\n\n", FILE_APPEND);
    }

    protected function getResponseBody($resp){

        return isset($resp['response']) ? $resp['response'] : null;
    }

    protected function assertResponseCode($resp, $code){

        self::assertTrue(isset($resp['code']) && $resp['code'] === $code);
    }

    protected function assertRequestSuccess($resp){

        self::assertTrue(isset($resp['code']) && $resp['code'] === Code::SUCCESS);
    }

    protected function assertResponseNotEmptyList($resp){

        self::assertTrue(isset($resp['code']) && $resp['code'] === Code::SUCCESS && isset($resp['response']) && count($resp['response']) > 0);
    }
}