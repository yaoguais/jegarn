<?php

namespace minions\http;
use minions\manager\UserManager;
use minions\model\User;
use minions\base\Code;
use minions\util\TextUtil;
use Yaf\Plugin_Abstract;
use Yaf\Request_Abstract;
use Yaf\Response_Abstract;

class ApiPluginBase extends Plugin_Abstract {

    public function routerStartup(Request_Abstract $request, Response_Abstract $response) {

    }

    public function routerShutdown(Request_Abstract $request, Response_Abstract $response) {

        if($resp = $this->checkAuth($request, $response)){
            throw new ApiException($resp->response, $resp->code);
        }
    }
    
    protected function checkAuth(Request_Abstract $request, Response_Abstract $response){

        $config = [
            'allow' => [
                'api-user-create' => 1,
                'api-user-login'  => 1,
                'api-user-recommend'  => 1,
                'api-user-info'  => 1,
                'api-group-recommend'  => 1,
            ]
        ];
        $id = strtolower($request->getModuleName().'-'.$request->getControllerName().'-'.$request->getActionName());
        if(!isset($config['allow'][$id])){
            $user = new User();
            $user->id = ApiRequest::getParam('uid');
            $token = ApiRequest::getParam('token');
            if(!$user->id || TextUtil::isEmptyString($token)){
                return new ApiResponse(Code::FAIL_PARAMETER_MISSING, 'uid or token is missing');
            }
            if($resp = UserManager::getInstance()->getUser($user)){
                return $resp;
            }
            if($user->token != $token){
                return new ApiResponse(Code::FAIL_USER_TOKEN_EXPIRE, null);
            }
            UserManager::getInstance()->setAuthorizedUser($user);
        }

        return null;
    }

    public function dispatchLoopStartup(Request_Abstract $request, Response_Abstract $response) {

    }

    public function preDispatch(Request_Abstract $request, Response_Abstract $response) {

    }

    public function postDispatch(Request_Abstract $request, Response_Abstract $response) {

    }

    public function dispatchLoopShutdown(Request_Abstract $request, Response_Abstract $response) {

    }
}