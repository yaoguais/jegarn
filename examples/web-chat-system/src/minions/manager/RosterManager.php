<?php

namespace minions\manager;
use minions\model\RosterGroup;
use minions\model\Roster;
use minions\model\User;
use minions\http\ApiResponse;
use minions\base\Code;
use minions\util\ConvertUtil;
use minions\util\JegarnUtil;
use minions\util\TextUtil;
use minions\db\Db;
use PDO;

class RosterManager extends BaseManager {

    const ADD_ROSTER =  'INSERT INTO `m_roster`(uid,target_id,status,create_time,update_time,remark,group_id,rank) VALUES (?,?,?,?,?,?,?,?)';
    const UPDATE_ROSTER = ' UPDATE `m_roster` SET status = ?,update_time = ?,remark = ?, group_id = ?,rank = ? where id = ?';
    const GET_ROSTER_BY_ID =  'select id,uid,target_id,status,remark,group_id,rank from `m_roster` where id = ?';
    const GET_ROSTER_BY_FRIEND =  'select id,uid,target_id,status,remark,group_id,rank from `m_roster` where uid = ? and target_id = ?';
    const DELETE_FULL_ROSTER   =  'delete from `m_roster` where (uid = ? and target_id = ?) or (uid = ? and target_id = ?)';
    const GET_ROSTER_LIST_BY_STATUS =  'select id,uid,target_id,status,remark,group_id,rank from `m_roster` where group_id = :group_id and uid = :uid and status = :status order by rank asc limit :offset,:limit';
    const GET_ROSTER_ALL_BY_STATUS =  'select id,uid,target_id,status,remark,group_id,rank from `m_roster` where group_id = :group_id and uid = :uid and status = :status order by rank asc';
    const MOVE_GROUP =  'update `m_roster` set group_id = ? where uid = ? and group_id = ?';

    public static function getInstance($class = __CLASS__){
        return parent::getInstance($class);
    }

    public function addRoster(Roster $model){

        $model->status = Roster::STATUS_REQUEST;
        if($resp = $this->checkRoster($model)){
            return $resp;
        }
        // check is record exists
        $dbModel = clone $model;
        $resp = $this->getRoster($dbModel);
        if(!$resp || $resp->code != Code::FAIL_ROSTER_NOT_EXISTS){
            return new ApiResponse(Code::FAIL_PERMISSION_DENY, 'repeat add');
        }

        $model->create_time = time();
        $model->update_time = 0;
        $model->rank = intval($model->rank);
        $model->group_id = intval($model->group_id);
        $dbManager = Db::getInstance();
        // get target's status
        $statement = $dbManager->prepare(self::GET_ROSTER_BY_FRIEND);
        if(!$statement->execute([$model->target_id, $model->uid])){
            return new ApiResponse(Code::FAIL_ROSTER_NOT_EXISTS, 'get target roster failed when add roster');
        }
        $targetResult = $statement->fetch(PDO::FETCH_ASSOC);
        if($targetResult){
            $target = ConvertUtil::arrayToObject($targetResult, new Roster(), ['id', 'uid', 'target_id', 'status', 'remark', 'group_id', 'rank']);
            if($target->status == Roster::STATUS_UNSUBSCRIBE){
                return new ApiResponse(Code::FAIL_ROSTER_UNSUBSCRIBE, 'you is in her ignore list');
            }
            if($target->status == Roster::STATUS_BLACK){
                return new ApiResponse(Code::FAIL_ROSTER_BLACK, 'you is in her black list');
            }
        }else{
            $dbManager->beginTransaction();
            $statement = $dbManager->prepare(self::ADD_ROSTER);
            if(!$statement->execute([$model->target_id, $model->uid, Roster::STATUS_RECEIVE, $model->create_time, $model->update_time, null, 0, 0])){
                $dbManager->rollBack();
                return new ApiResponse(Code::FAIL_DATABASE_ERROR,'create target roster failed');
            }
        }
        $statement = $dbManager->prepare(self::ADD_ROSTER);
        if(!$statement->execute([$model->uid, $model->target_id, $model->status, $model->create_time, $model->update_time, $model->remark, $model->group_id, $model->rank])){
            if(!$targetResult){
                $dbManager->rollBack();
            }
            return new ApiResponse(Code::FAIL_DATABASE_ERROR,'create roster failed');
        }
        $model->id = $dbManager->lastInsertId();
        if(!$targetResult){
            $dbManager->commit();
        }
        JegarnUtil::sendFriendRequestNotification($model->uid, $model->target_id);

        return null;
    }

    public function updateRoster(Roster $model){

        if(!$model->uid || !$model->target_id){
            return new ApiResponse(Code::FAIL_ROSTER_NOT_EXISTS, 'uid or target_id is empty');
        }
        if($model->status != Roster::STATUS_UNSUBSCRIBE && $model->status != Roster::STATUS_AGREE
            && $model->status != Roster::STATUS_REFUSED && $model->status != Roster::STATUS_BLACK){
            return new ApiResponse(Code::FAIL_ROSTER_STATUS, 'status must be unsubscribe or receive or refused or blacklist');
        }
        if($resp = $this->checkRoster($model)){
            return $resp;
        }
        $modelCopy = clone $model;
        if($resp = $this->getRoster($modelCopy)){
            return $resp;
        }
        $model->id = $modelCopy->id;
        if($model->uid != $modelCopy->uid){
            return new ApiResponse(Code::FAIL_ROSTER_NOT_EXISTS, 'other\'s roster');
        }
        $fullSame = true;
        $compareField = ['status', 'remark', 'group_id', 'rank'];
        foreach($compareField as $f){
            if($model->$f != $modelCopy->$f){
                $fullSame = false;
                break;
            }
        }
        if($fullSame){
            return null;
        }
        $model->update_time = time();
        $dbManager = Db::getInstance();
        // receive when i am her friend or her ask to make
        $roster = null;
        if($model->status == Roster::STATUS_AGREE){
            $roster = new Roster();
            $roster->uid = $model->target_id;
            $roster->target_id = $model->uid;
            if($resp = $this->getRoster($roster)){
                return $resp;
            }
            if($roster->status != Roster::STATUS_REQUEST && $roster->status != Roster::STATUS_AGREE){
                return new ApiResponse(Code::FAIL_ROSTER_STATUS,'status must be ask or agree');
            }
        }
        $dbManager->beginTransaction();
        $statement = $dbManager->prepare(self::UPDATE_ROSTER);
        if(!$statement->execute([$model->status, $model->update_time, $model->remark, $model->group_id, $model->rank, $model->id])){
            $dbManager->rollBack();
            return new ApiResponse(Code::FAIL_DATABASE_ERROR,'update roster failed');
        }
        if($roster && $roster->status == Roster::STATUS_REQUEST){
            $roster->status = Roster::STATUS_AGREE;
            $roster->update_time = time();
            if(!$statement->execute([$roster->status, $roster->update_time, $roster->remark, $roster->group_id, $roster->rank, $roster->id])){
                $dbManager->rollBack();
                return new ApiResponse(Code::FAIL_DATABASE_ERROR,'update target roster failed');
            }
            JegarnUtil::sendFriendAgreeNotification($roster->target_id, $roster->uid);
        }
        $dbManager->commit();
        // if status not changed, it may change friend attributes. so don not need notify
        if($modelCopy->status != Roster::STATUS_AGREE && $model->status == Roster::STATUS_AGREE){
            JegarnUtil::sendFriendAgreeNotification($model->target_id, $model->uid);
        }else if($modelCopy->status != Roster::STATUS_REFUSED && $model->status == Roster::STATUS_REFUSED){
            JegarnUtil::sendFriendRefusedNotification($model->target_id, $model->uid);
        }
        return null;
    }

    public function deleteRoster(Roster $model){

        if(!$model->uid || !$model->target_id){
            return new ApiResponse(Code::FAIL_ROSTER_NOT_EXISTS, 'uid or target_id is empty');
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::DELETE_FULL_ROSTER);
        if(!$statement->execute([$model->uid,$model->target_id,$model->target_id,$model->uid])){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR,'delete roster failed');
        }

        return null;
    }

    public function getRoster(Roster $model){

        $dbManager = Db::getInstance();
        if($model->id){
            $statement = $dbManager->prepare(self::GET_ROSTER_BY_ID);
            $executeRet = $statement->execute([$model->id]);
        }else if($model->uid && $model->target_id){
            $statement = $dbManager->prepare(self::GET_ROSTER_BY_FRIEND);
            $executeRet = $statement->execute([$model->uid, $model->target_id]);
        }else{
            return new ApiResponse(Code::FAIL_ROSTER_NOT_EXISTS, 'id is empty || uid & target_id is empty');
        }
        if(!$executeRet){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR,'get roster failed');
        }
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if(!$result){
            return new ApiResponse(Code::FAIL_ROSTER_NOT_EXISTS, 'get roster failed');
        }
        ConvertUtil::arrayToObject($result, $model, ['id', 'uid', 'target_id', 'status', 'remark', 'group_id', 'rank']);

        return null;
    }

    /**
     * @param Roster $model
     * @return Roster[]
     */
    public function getRosterAll(Roster $model){

        if(!$model->uid){
            return null;
        }
        if(!$model->checkStatus()){
            return null;
        }
        $model->group_id = intval($model->group_id);
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::GET_ROSTER_ALL_BY_STATUS);
        $statement->bindValue(':group_id', $model->group_id, PDO::PARAM_INT);
        $statement->bindValue(':uid', $model->uid, PDO::PARAM_INT);
        $statement->bindValue(':status', $model->status, PDO::PARAM_INT);
        if(!$statement->execute()){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR,'get roster list array failed');
        }

        return $statement->fetchAll(PDO::FETCH_CLASS, 'minions\model\Roster');
    }

    public function moveGroup(Roster $model, RosterGroup $from, RosterGroup $to){

        if(!$model->uid){
            return new ApiResponse(Code::FAIL_USER_NOT_EXISTS, 'uid is empty');
        }
        if($from->id == $to->id){
            return new ApiResponse(Code::FAIL_MOVE_GROUP, 'from is same as to');
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::MOVE_GROUP);
        if(!$statement->execute([$to->id, $model->uid, $from->id])){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR,'move group failed');
        }

        return null;
    }

    protected function checkRoster(Roster $model){

        if(!$model->uid){
            return new ApiResponse(Code::FAIL_USER_NOT_EXISTS, 'uid is empty');
        }
        if(!$model->target_id){
            return new ApiResponse(Code::FAIL_USER_NOT_EXISTS, 'target_id is empty');
        }
        if($model->uid == $model->target_id){
            return new ApiResponse(Code::FAIL_PERMISSION_DENY, 'target_id is uid');
        }
        if(!$model->checkStatus()){
            return new ApiResponse(Code::FAIL_ROSTER_STATUS, 'status is error['.$model->status.']');
        }
        $friend = new User();
        $friend->id = $model->target_id;
        if($resp = UserManager::getInstance()->getUser($friend)){
            return new ApiResponse(Code::FAIL_USER_NOT_EXISTS, 'friend not exists');
        }
        if($model->group_id > 0){
            $grManager = RosterGroupManager::getInstance();
            if($groupRoster = $grManager->getRosterGroupById($model->group_id)){
                if($groupRoster->uid != $model->uid){
                    return new ApiResponse(Code::FAIL_GROUP_ROSTER_NOT_EXISTS, 'other\'s group roster');
                }
            }else{
                return new ApiResponse($grManager->getLastErrorCode(), $grManager->getLastErrorString());
            }
        }

        return null;
    }
}