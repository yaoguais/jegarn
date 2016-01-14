<?php

use minions\manager\UserManager;
use minions\model\Base;
use minions\model\RosterGroup;
use minions\model\Roster;
use minions\manager\RosterManager;
use minions\manager\RosterGroupManager;
use minions\http\ApiRequest;
use minions\http\ApiResponse;
use minions\http\ApiControllerBase;
use minions\base\Code;

class RosterController extends ApiControllerBase {

    public function createAction(){

        if($resp = $this->checkUserInput(['target_id'], self::POST)){
            $resp->flush();
            return false;
        }
        $model = new Roster();
        $model->uid = $this->user->id;
        $this->setByUserInput($model, ['target_id', 'remark', 'group_id', 'rank']);
        if($resp = RosterManager::getInstance()->addRoster($model)){
            $resp->flush();
        }else{
            (new ApiResponse(Code::SUCCESS, $model->toArray()))->flush();
        }
        return false;
    }

    public function updateAction(){

        if($resp = $this->checkUserInput(['target_id', 'status', 'remark', 'group_id', 'rank'], self::POST)){
            $resp->flush();
            return false;
        }
        $model = new Roster();
        $model->uid = $this->user->id;
        $this->setByUserInput($model, ['target_id', 'status', 'remark', 'group_id', 'rank']);
        if($resp = RosterManager::getInstance()->updateRoster($model)){
            $resp->flush();
        }else{
            (new ApiResponse(Code::SUCCESS, $model->toArray()))->flush();
        }
        return false;
    }

    public function infoAction(){
        if($resp = $this->checkUserInput(['target_id'], self::GET)){
            $resp->flush();
            return false;
        }
        $model = new Roster();
        $model->uid = $this->user->id;
        $this->setByUserInput($model, ['target_id']);
        if($resp = RosterManager::getInstance()->getRoster($model)){
            $resp->flush();
        }else{
            (new ApiResponse(Code::SUCCESS, $model->toArray()))->flush();
        }
        return false;
    }

    public function listAction(){

        if($resp = $this->checkUserInput(null, self::GET)){
            $resp->flush();
            return false;
        }
        $roster = new Roster();
        $roster->uid = $this->user->id;
        $roster->group_id = ApiRequest::getParam('group_id');
        $roster->status = ApiRequest::getParam('status');
        $rosters = RosterManager::getInstance()->getRosterAll($roster);
        $list = [];
        foreach($rosters as $r){
            $list[] = $r->toArray();
        }

        (new ApiResponse(Code::SUCCESS, $list))->flush();
        return false;
    }

    public function list_allAction(){

        if($resp = $this->checkUserInput(null, self::GET)){
            $resp->flush();
            return false;
        }
        $groupList = RosterGroupManager::getInstance()->getUserRosterGroup($this->user->id);
        $rosterManager = RosterManager::getInstance();
        $userManager = UserManager::getInstance();
        $list = [];
        /* @var RosterGroup $group */
        foreach($groupList as $group){
            $roster = new Roster();
            $roster->group_id = $group->id;
            $roster->uid = $group->uid;
            $roster->status = Roster::STATUS_AGREE;
            $rosterList = $rosterManager->getRosterAll($roster);
            $row = $group->toArray();
            if($rosterList){
                $row['rosters'] = [];
                foreach($rosterList as $rst){
                    $rosterData = $rst->toArray();
                    $user = $userManager->getUserById($rst->target_id);
                    if($user){
                        $user->makeSecret();
                        $rosterData['user'] = $user->toArray();
                    }else{
                        $rosterData['user'] = null;
                    }
                    $row['rosters'][] = $rosterData;
                }
            }
            $list[] = $row;
        }

        (new ApiResponse(Code::SUCCESS, $list))->flush();
        return false;
    }

    public function create_groupAction(){

        if($resp = $this->checkUserInput(['name'], self::POST)){
            $resp->flush();
            return false;
        }
        $model = new RosterGroup();
        $model->uid = $this->user->id;
        $this->setByUserInput($model, ['name', 'rank']);

        if($resp = RosterGroupManager::getInstance()->addGroupRoster($model)){
            $resp->flush();
        }else{
            (new ApiResponse(Code::SUCCESS, $model->toArray()))->flush();
        }
        return false;
    }

    public function update_groupAction(){

        if($resp = $this->checkUserInput(['group_id', 'name'], self::POST)){
            $resp->flush();
            return false;
        }
        $model = new RosterGroup();
        $model->uid = $this->user->id;
        $this->setByUserInput($model, ['group_id' => 'id', 'name', 'rank']);

        if($resp = RosterGroupManager::getInstance()->updateGroupRoster($model)){
            $resp->flush();
        }else{
            (new ApiResponse(Code::SUCCESS, $model->toArray()))->flush();
        }
        return false;
    }

    public function delete_groupAction(){

        if($resp = $this->checkUserInput(['group_id'], self::POST)){
            $resp->flush();
            return false;
        }
        $model = new RosterGroup();
        $model->uid = $this->user->id;
        $this->setByUserInput($model, ['group_id' => 'id']);

        if($resp = RosterGroupManager::getInstance()->deleteGroupRoster($model)){
            $resp->flush();
        }else{
            (new ApiResponse(Code::SUCCESS, $model->toArray()))->flush();
        }
        return false;
    }
}