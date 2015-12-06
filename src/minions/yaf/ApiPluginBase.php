<?php

namespace minions\yaf;
use \minions\manager\UserManager;
use \minions\model\User;
use minions\request\ApiRequest;
use \minions\response\Code;
use \minions\response\ApiResponse;
use \minions\util\Text;
use \Yaf\Plugin_Abstract;
use \Yaf\Request_Abstract;
use \Yaf\Response_Abstract;

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
            'white' => [
                'api-user-create' => 1,
                'api-user-login'  => 1
            ]
        ];
        $id = strtolower($request->getModuleName().'-'.$request->getControllerName().'-'.$request->getActionName());
        if(!isset($config['white'][$id])){
            $user = new User();
            $user->id = ApiRequest::getParam('uid');
            $token = ApiRequest::getParam('token');
            if(!$user->id || Text::isEmptyString($token)){
                return ApiResponse::newInstance(Code::FAIL_PARAMETER_MISSING, 'uid or token is missing');
            }
            if($resp = UserManager::getInstance()->getUser($user)){
                return $resp;
            }
            if($user->token != $token){
                return ApiResponse::newInstance(Code::FAIL_USER_TOKEN_EXPIRE, null);
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

        ApiResponse::response();
    }
}