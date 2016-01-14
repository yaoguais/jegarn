<?php

use minions\base\Code;
use minions\manager\GroupManager;
use minions\manager\GroupUserManager;
use minions\model\Group;
use minions\model\GroupUser;
use minions\http\ApiRequest;
use minions\http\ApiResponse;
use minions\http\ApiControllerBase;

class GroupController extends ApiControllerBase {

    public function createAction(){

        if($resp = $this->checkUserInput(['type', 'name', 'description'], self::POST)){
            $resp->flush();
            return false;
        }
        $model = new Group();
        $model->uid = $this->user->id;
        $this->setByUserInput($model, ['type', 'name', 'description']);
        if($resp = GroupManager::getInstance()->addGroup($model)){
            $resp->flush();
        }else{
            (new ApiResponse(Code::SUCCESS, $model->toArray()))->flush();
        }
        return false;
    }

    public function updateAction(){

        if($resp = $this->checkUserInput(['group_id', 'name', 'description'], self::POST)){
            $resp->flush();
            return false;
        }
        $model = new Group();
        $model->uid = $this->user->id;
        $this->setByUserInput($model, ['group_id' => 'id', 'name', 'description']);
        if($resp = GroupManager::getInstance()->updateGroup($model)){
            $resp->flush();
        }else{
            (new ApiResponse(Code::SUCCESS, $model->toArray()))->flush();
        }
        return false;
    }

    public function deleteAction(){

        if($resp = $this->checkUserInput(['group_id'], self::POST)){
            $resp->flush();
            return false;
        }
        $model = new Group();
        $model->uid = $this->user->id;
        $this->setByUserInput($model, ['group_id' => 'id']);
        if($resp = GroupManager::getInstance()->removeGroup($model)){
            $resp->flush();
        }else{
            (new ApiResponse(Code::SUCCESS, $model->toArray()))->flush();
        }
        return false;
    }

    public function infoAction(){
        if($resp = $this->checkUserInput(['group_id'], self::GET)){
            $resp->flush();
            return false;
        }
        $groupManager = GroupManager::getInstance();
        if($model = $groupManager->getGroupById(ApiRequest::getParam('group_id'))){
            (new ApiResponse(Code::SUCCESS, $model->toArray()))->flush();
        }else{
            (new ApiResponse($groupManager->getLastErrorCode(), $groupManager->getLastErrorString()))->flush();
        }
        return false;
    }

    public function listAction(){

        if($resp = $this->checkUserInput(['type', 'status'], self::GET)){
            $resp->flush();
            return false;
        }
        $model = new Group();
        $this->setByUserInput($model, ['type']);
        $groupUser = new GroupUser();
        $groupUser->uid = $this->user->id;
        $groupUser->status = ApiRequest::getParam('status');
        $groupManager = GroupManager::getInstance();
        $objects = $groupManager->getUserGroups($model, $groupUser);
        if(is_array($objects)){
            $list = [];
            foreach($objects as $o){
                $list[] = $o->toArray();
            }
            (new ApiResponse(Code::SUCCESS, $list))->flush();
        }else{
            (new ApiResponse($groupManager->getLastErrorCode(), $groupManager->getLastErrorString()))->flush();
        }
        return false;
    }

    public function recommendAction(){
        if($resp = $this->checkUserInput(['type'], self::GET)){
            $resp->flush();
            return false;
        }
        $model = new Group();
        $this->setByUserInput($model, ['type']);
        $groupManager = GroupManager::getInstance();
        $objects = $groupManager->getHotGroups($model);
        if(is_array($objects)){
            $list = [];
            foreach($objects as $o){
                $list[] = $o->toArray();
            }
            (new ApiResponse(Code::SUCCESS, $list))->flush();
        }else{
            (new ApiResponse($groupManager->getLastErrorCode(), $groupManager->getLastErrorString()))->flush();
        }
        return false;
    }

    public function joinAction(){

        if($resp = $this->checkUserInput(['group_id'], self::POST)){
            $resp->flush();
            return false;
        }
        $group = new Group();
        $group->id = ApiRequest::getParam('group_id');
        $groupUser = new GroupUser();
        $groupUser->gid = $group->id;
        $groupUser->uid = $this->user->id;

        if($resp = GroupUserManager::getInstance()->joinGroup($group, $groupUser)){
            $resp->flush();
        }else{
            (new ApiResponse(Code::SUCCESS, $groupUser->toArray()))->flush();
        }
        return false;
    }

    public function quitAction(){

        if($resp = $this->checkUserInput(['group_id'], self::POST)){
            $resp->flush();
            return false;
        }
        $group = new Group();
        $group->id = ApiRequest::getParam('group_id');
        $groupUser = new GroupUser();
        $groupUser->gid = $group->id;
        $groupUser->uid = $this->user->id;

        if($resp = GroupUserManager::getInstance()->quitGroup($group, $groupUser)){
            $resp->flush();
        }else{
            (new ApiResponse(Code::SUCCESS, $groupUser->toArray()))->flush();
        }
        return false;
    }

    public function manage_userAction(){

        if($resp = $this->checkUserInput(['gid', 'user_id', 'status', 'permission'], self::POST)){
            $resp->flush();
            return false;
        }
        $model = new GroupUser();
        $this->setByUserInput($model, ['gid', 'user_id' => 'uid', 'status', 'permission']);
        if($resp = GroupUserManager::getInstance()->manageUser($model, $this->user)){
            $resp->flush();
        }else{
            (new ApiResponse(Code::SUCCESS, $model->toArray()))->flush();
        }
        return false;
    }

    public function list_userAction(){

        if($resp = $this->checkUserInput(['group_id'], self::GET)){
            $resp->flush();
            return false;
        }
        if(!($group = GroupManager::getInstance()->getGroupById(ApiRequest::getParam('group_id')))){
            (new ApiResponse(Code::FAIL_GROUP_NOT_EXISTS, null))->flush();
            return false;
        }
        $groupUserManager = GroupUserManager::getInstance();
        if(!($groupUser = $groupUserManager->getGroupUserByGidUid($group->id, $this->user->id))){
            (new ApiResponse(Code::FAIL_GROUP_USER_NOT_EXISTS, null))->flush();
            return false;
        }
        $status = ApiRequest::getParam('status');
        $groupUserModel = new GroupUser();
        if($group->type == Group::TYPE_CHATROOM){
            $groupUserModel->status = GroupUser::STATUS_AGREE;
        }else/* if($group->type == Group::TYPE_GROUP)*/{
            $groupUserModel->status = $status;
            if(!$groupUserModel->checkStatus()){
                (new ApiResponse(Code::FAIL_GROUP_USER_STATUS, 'chatroom must set status'))->flush();
                return false;
            }
            if($status != GroupUser::STATUS_AGREE && $groupUser->permission < GroupUser::PERMISSION_ADMIN){
                (new ApiResponse(Code::FAIL_GROUP_USER_PERMISSION, null))->flush();
                return false;
            }
        }
        $objects = $groupUserManager->getAllGroupUser($group, $groupUserModel, $this->getOffset(), $this->getLimit());
        if(is_array($objects)){
            $list = [];
            foreach($objects as $o){
                $list[] = $o->toArray();
            }
            (new ApiResponse(Code::SUCCESS, $list))->flush();
        }else{
            (new ApiResponse($groupUserManager->getLastErrorCode(), $groupUserManager->getLastErrorString()))->flush();
        }
        return false;
    }
}