<?php

namespace minions\manager;
use minions\model\RosterGroup;
use minions\model\Roster;
use minions\http\ApiResponse;
use minions\base\Code;
use minions\util\ConvertUtil;
use minions\db\Db;
use PDO;

class RosterGroupManager extends BaseManager {

    const ADD_ROSTER_GROUP       =  'insert into `m_roster_group`(uid,name,rank) VALUES (?,?,?)';
    const GET_ROSTER_GROUP_BY_ID =  'select id,uid,name from `m_roster_group` where id = ?';
    const UPDATE_ROSTER_GROUP    =  'update `m_roster_group` set name = ?,rank = ? where id = ?';
    const DELETE_ROSTER_GROUP    =  'delete from `m_roster_group` where id = ?';
    const GET_USER_ROSTER_GROUP  =  'select id,uid,name from `m_roster_group` where uid = ? order by rank asc';

    public static function getInstance($class = __CLASS__){
        return parent::getInstance($class);
    }

    public function addGroupRoster(RosterGroup $model){

        if(!$model->uid){
            return new ApiResponse(Code::FAIL_USER_NOT_EXISTS, 'uid is empty');
        }
        $model->rank = intval($model->rank);
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::ADD_ROSTER_GROUP);
        if(!$statement->execute([$model->uid, $model->name, $model->rank])){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'create roster group failed');
        }
        $model->id = $dbManager->lastInsertId();

        return null;
    }

    public function updateGroupRoster(RosterGroup $model){

        if(!$model->id){
            return new ApiResponse(Code::FAIL_GROUP_ROSTER_NOT_EXISTS, 'id is empty');
        }
        if(!($dbModel = $this->getRosterGroupById($model->id))){
            return new ApiResponse($this->getLastErrorCode(), $this->getLastErrorString());
        }
        if($dbModel->uid != $model->uid){
            return new ApiResponse(Code::FAIL_GROUP_ROSTER_NOT_EXISTS, 'other\'s roster group');
        }
        if($model->name == $dbModel->name && $model->rank == $dbModel->rank){
            return null;
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::UPDATE_ROSTER_GROUP);
        if(!$statement->execute([$model->name, $model->rank, $model->id])){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'update roster group failed');
        }

        return null;
    }

    public function deleteGroupRoster(RosterGroup $model){

        if(!$model->id){
            return new ApiResponse(Code::FAIL_GROUP_ROSTER_NOT_EXISTS, 'id is empty');
        }
        if(!($dbModel = $this->getRosterGroupById($model->id))){
            return new ApiResponse($this->getLastErrorCode(), $this->getLastErrorString());
        }
        if($dbModel->uid != $model->uid){
            return new ApiResponse(Code::FAIL_GROUP_ROSTER_NOT_EXISTS, 'other\'s roster group');
        }
        $dbManager = Db::getInstance();
        $dbManager->beginTransaction();
        $statement = $dbManager->prepare(self::DELETE_ROSTER_GROUP);
        if(!$statement->execute([$model->id])){
            $dbManager->rollBack();
            return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'delete roster group failed');
        }
        // update roster of this group to default group
        $roster = new Roster();
        $roster->uid = $model->uid;
        $groupTo = new RosterGroup();
        $groupTo->id = 0;
        if($resp = RosterManager::getInstance()->moveGroup($roster, $model, $groupTo)){
            $dbManager->rollBack();
            return $resp;
        }
        $dbManager->commit();

        return null;
    }

    public function getRosterGroupById($id){

        if(!$id){
            $this->setLastError(Code::FAIL_GROUP_ROSTER_NOT_EXISTS, 'id is empty');
            return null;
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::GET_ROSTER_GROUP_BY_ID);
        if(!$statement->execute([$id])){
            $this->setLastError(Code::FAIL_DATABASE_ERROR, 'get roster group failed');
            return null;
        }
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $statement->setFetchMode(PDO::FETCH_CLASS, 'minions\model\RosterGroup');
        
        return  $statement->fetch(PDO::FETCH_CLASS);
    }

    public function getUserRosterGroup($uid){
        if(!$uid){
            $this->setLastError(Code::FAIL_GROUP_ROSTER_NOT_EXISTS, 'uid is empty');
            return null;
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::GET_USER_ROSTER_GROUP);
        if(!$statement->execute([$uid])){
            $this->setLastError(Code::FAIL_DATABASE_ERROR, 'get roster groups failed');
            return null;
        }

        $list = $statement->fetchAll(PDO::FETCH_CLASS, 'minions\model\RosterGroup');

        return $this->addDefaultGroupToList($list, $this->getUserDefaultGroup($uid));
    }

    protected function getUserDefaultGroup($uid){
        $group = new RosterGroup();
        $group->id = 0;
        $group->uid = $uid;
        $group->name = RosterGroup::DEFAULT_NAME;
        $group->rank = -1;
        return $group;
    }

    protected function addDefaultGroupToList($list, RosterGroup $group){
        if(!is_array($list)){
            $list = [];
        }
        array_unshift($list, $group);
        return $list;
    }
}