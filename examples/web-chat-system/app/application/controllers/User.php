<?php

use minions\manager\LoginLogManager;
use minions\upload\UploadFile;
use minions\manager\UserManager;
use minions\model\User;
use minions\http\ApiRequest;
use minions\http\ApiResponse;
use minions\base\Code;
use minions\util\TextUtil;
use minions\upload\Uploader;
use minions\http\ApiControllerBase;

class UserController extends ApiControllerBase {

    public function createAction(){

        if($resp = $this->checkUserInput(['account', 'password', 'nick'], self::POST)){
            $resp->flush();
            return false;
        }
        $model = new User();
        /*if($avatar = UploadFile::getInstanceByName('avatar')){
            if(!($path = Uploader::saveFile($avatar,'avatar', Uploader::TYPE_IMAGE))){
                (new ApiResponse(Uploader::getLastErrorCode(), null))->flush();
                return false;
            }
            $model->avatar = $path;
        }*/
        if($resp = $this->setByUserInput($model,['account' => 'username', 'password', 'nick'])){
            $resp->flush();
            return false;
        }
        $model->motto = ApiRequest::getParam('motto');
        if($resp = UserManager::getInstance()->addUserEx($model)){
            $resp->flush();
        }else{
            (new ApiResponse(Code::SUCCESS, $model->toArray()))->flush();
        }
        return false;
    }

    public function updateAction(){

        $nick = ApiRequest::getParam('nick');
        $avatar = UploadFile::getInstanceByName('avatar');
        if(TextUtil::isEmptyString($nick) || !$avatar){
            (new ApiResponse(Code::FAIL_PARAMETER_MISSING, 'nick avatar at least one'))->flush();
            return false;
        }
        if(!$avatar && $nick == $this->user->nick){
            (new ApiResponse(Code::SUCCESS, $this->user->toArray()))->flush();
            return false;
        }
        $model = $this->user;
        if($avatar){
            if(!($path = Uploader::saveFile($avatar,'avatar', Uploader::TYPE_IMAGE))){
                (new ApiResponse(Uploader::getLastErrorCode(), null))->flush();
                return false;
            }
            $model->avatar = $path;
        }
        $model->nick = $nick ? : $model->nick;
        if($resp = UserManager::getInstance()->updateUser($model)){
            $resp->flush();
        }else{
            (new ApiResponse(Code::SUCCESS, $model->toArray()))->flush();
        }
        return false;
    }

    public function infoAction(){
        if($resp = $this->checkUserInput(['user_id'], self::GET)){
            $resp->flush();
            return false;
        }
        $model = UserManager::getInstance()->getUserById(ApiRequest::getParam('user_id'));
        if(!$model){
            (new ApiResponse(Code::FAIL_USER_NOT_EXISTS, null))->flush();
        }else{
            (new ApiResponse(Code::SUCCESS, $model->toArray()))->flush();
        }
        return false;
    }

    public function loginAction(){

        if($resp = $this->checkUserInput(['account', 'password'], self::POST)){
            $resp->flush();
            return false;
        }
        $model = new User();
        $this->setByUserInput($model,['account' => 'username', 'password']);
        $userManager = UserManager::getInstance();
        if($resp = $userManager->login($model)){
            $resp->flush();
        }else{
            (new ApiResponse(Code::SUCCESS, $userManager->getAuthorizedUser()->toArray()))->flush();
        }
        return false;
    }

    public function recommendAction(){
        if($resp = $this->checkUserInput(null, self::GET)){
            $resp->flush();
            return false;
        }
        // find last login success users, limit 30, and return
        $list = [];
        if($uidList = LoginLogManager::getInstance()->getLatestSuccessUser(0,20)){
            $userManager = UserManager::getInstance();
            foreach($uidList as $uid){
                $user = new User();
                $user->id = $uid;
                if(!$userManager->getUser($user)){
                    $row = $user->toArray();
                    unset($row['token']);
                    $list[] = $row;
                }
            }
        }
        (new ApiResponse(Code::SUCCESS, $list))->flush();
        return false;
    }
}