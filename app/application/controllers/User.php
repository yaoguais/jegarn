<?php

use minions\base\UploadFile;
use minions\manager\UserManager;
use minions\model\User;
use minions\request\ApiRequest;
use minions\response\ApiResponse;
use minions\response\Code;
use minions\util\Text;
use minions\util\Upload;
use minions\yaf\ApiControllerBase;

class UserController extends ApiControllerBase {

    public function createAction(){

        if($resp = $this->checkUserInput(['account', 'password', 'nick'], self::POST)){
            return $resp;
        }
        $model = new User();
        if($avatar = UploadFile::getInstanceByName('avatar')){
            if(!($path = Upload::saveFile($avatar,'avatar', Upload::TYPE_IMAGE))){
                return ApiResponse::newInstance(Upload::getLastError(), null);
            }
            $model->avatar = $path;
        }

        if($resp = $this->setByUserInput($model,['account' => 'username', 'password', 'nick'])){
            return $resp;
        }
        if($resp = UserManager::getInstance()->addUserEx($model)){
            return $resp;
        }

        return ApiResponse::newInstance(Code::SUCCESS, $model->toArray());
    }

    public function updateAction(){

        $nick = ApiRequest::getParam('nick');
        $avatar = UploadFile::getInstanceByName('avatar');
        if(Text::isEmptyString($nick) || !$avatar){
            return ApiResponse::newInstance(Code::FAIL_PARAMETER_MISSING, 'nick avatar at least one');
        }
        if(!$avatar && $nick == $this->user->nick){
            return ApiResponse::newInstance(Code::SUCCESS, $this->user->toArray());
        }
        $model = $this->user;
        if($avatar){
            if(!($path = Upload::saveFile($avatar,'avatar', Upload::TYPE_IMAGE))){
                return ApiResponse::newInstance(Upload::getLastError(), null);
            }
            $model->avatar = $path;
        }
        $model->nick = $nick ? : $model->nick;
        if($resp = UserManager::getInstance()->updateUser($model)){
            return $resp;
        }

        return ApiResponse::newInstance(Code::SUCCESS, $model->toArray());
    }

    public function loginAction(){

        if($resp = $this->checkUserInput(['account', 'password'], self::POST)){
            return $resp;
        }
        $model = new User();
        $this->setByUserInput($model,['account' => 'username', 'password']);
        $userManager = UserManager::getInstance();
        if($resp = $userManager->login($model)){
            return $resp;
        }

        return ApiResponse::newInstance(Code::SUCCESS, $userManager->getAuthorizedUser()->toArray());
    }
}