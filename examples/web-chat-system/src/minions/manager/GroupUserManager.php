<?php

namespace minions\manager;
use minions\model\Group;
use minions\model\GroupUser;
use minions\model\User;
use minions\http\ApiResponse;
use minions\base\Code;
use minions\util\ConvertUtil;
use minions\db\Db;
use minions\util\JegarnUtil;
use PDO;

class GroupUserManager extends BaseManager {

    const ADD_USER =  'insert into `m_group_user`(gid,uid,permission,create_time,status,remark) values(?,?,?,?,?,?)';
    const GET_USER =  'select id,gid,uid,permission,create_time,status,remark from `m_group_user` where id = ?';
    const GET_GROUP_USER =  'select id,gid,uid,permission,create_time,status,remark from `m_group_user` where gid = ? and uid = ?';
    const UPDATE_USER =  'update `m_group_user` set gid=?,uid=?,permission=?,status=?,remark=? where id = ?';
    const UPDATE_USER_STATUS =  'update `m_group_user` set status=? where id = ?';
    const UPDATE_USER_PERMISSION =  'update `m_group_user` set permission=? where id = ?';
    const UPDATE_USER_STATUS_AND_PERMISSION =  'update `m_group_user` set status=?,permission=? where id = ?';
    const GET_SECOND_USER =  'select id,gid,uid,permission,create_time,status,remark from `m_group_user` where gid = ? order by id ASC limit 1,1';
    const DELETE_BY_ID   =  'delete from `m_group_user` where id = ?';
    const DELETE_ALL_USER =  'delete from `m_group_user` where gid = ?';
    const DELETE_GROUP_USER =  'delete from `m_group_user` where gid = ? and uid = ?';
    const GET_GROUP_USER_COUNT =  'select count(*) from `m_group_user` where gid = ?';
    const GET_ALL_USERS =  'select id,gid,uid,permission,create_time,status,remark from `m_group_user` where gid = :gid limit :offset,:limit';
    const GET_ALL_USERS_WITH_STATUS =  'select id,gid,uid,permission,create_time,status,remark from `m_group_user` where gid = :gid and status = :status limit :offset,:limit';

    public static function getInstance($class = __CLASS__){
        return parent::getInstance($class);
    }

    public function addGroupUser(Group $group, GroupUser $model){

        $model->gid = $group->id;
        $model->create_time = time();
        if($resp = $this->checkGroupUser($model)){
            return $resp;
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::ADD_USER);
        if(!$statement->execute([$model->gid, $model->uid, $model->permission, $model->create_time, $model->status, $model->remark])){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'create group user failed');
        }
        $model->id = $dbManager->lastInsertId();

        return null;
    }

    public function removeGroupUser(Group $group, GroupUser $model){

        if(!$group->id){
            return new ApiResponse(Code::FAIL_GROUP_NOT_EXISTS, 'id is empty');
        }
        if(!$model->uid){
            return new ApiResponse(Code::FAIL_USER_NOT_EXISTS, 'uid is empty');
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::DELETE_GROUP_USER);
        if(!$statement->execute([$group->id, $model->uid])){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'delete group user failed');
        }

        return null;
    }

    public function removeGroupUserById(GroupUser $model){

        if(!$model->id){
            return new ApiResponse(Code::FAIL_GROUP_USER_NOT_EXISTS, 'id is empty');
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::DELETE_BY_ID);
        if(!$statement->execute([$model->id])){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'delete group user with id failed');
        }

        return null;
    }

    public function updateGroupUserStatus(GroupUser $model){

        if(!$model->id){
            return new ApiResponse(Code::FAIL_GROUP_USER_NOT_EXISTS, 'id is empty');
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::UPDATE_USER_STATUS);
        if(!$statement->execute([$model->status, $model->id])){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'update group user status failed');
        }

        return null;
    }

    public function updateGroupUserPermission(GroupUser $model){

        if(!$model->id){
            return new ApiResponse(Code::FAIL_GROUP_USER_NOT_EXISTS, 'id is empty');
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::UPDATE_USER_PERMISSION);
        if(!$statement->execute([$model->permission, $model->id])){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'update group user permission failed');
        }

        return null;
    }

    public function updateGroupUserStatusAndPermission(GroupUser $model){

        if(!$model->id){
            return new ApiResponse(Code::FAIL_GROUP_USER_NOT_EXISTS, 'id is empty');
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::UPDATE_USER_STATUS_AND_PERMISSION);
        if(!$statement->execute([$model->status, $model->permission, $model->id])){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'update group user status & permission failed');
        }

        return null;
    }

    /**
     * @param $id
     * @return GroupUser
     * @throws \Exception
     */
    public function getGroupUserById($id){

        if(!$id){
            $this->setLastError(Code::FAIL_GROUP_NOT_EXISTS, 'id is empty');
            return null;
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::GET_USER);
        if(!$statement->execute([$id])){
            $this->setLastError(Code::FAIL_DATABASE_ERROR,'execute to get group user failed');
            return null;
        }
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $statement->setFetchMode(PDO::FETCH_CLASS, 'minions\model\GroupUser');

        return $statement->fetch(PDO::FETCH_CLASS);
    }

    /**
     * @param $gid
     * @param $uid
     * @return GroupUser
     * @throws \Exception
     */
    public function getGroupUserByGidUid($gid, $uid){

        if(!$uid || !$gid){
            $this->setLastError(Code::FAIL_GROUP_NOT_EXISTS, 'id is empty');
            return null;
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::GET_GROUP_USER);
        if(!$statement->execute([$gid, $uid])){
            $this->setLastError(Code::FAIL_DATABASE_ERROR,'execute to get group user failed');
            return null;
        }
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $statement->setFetchMode(PDO::FETCH_CLASS, 'minions\model\GroupUser');

        return $statement->fetch(PDO::FETCH_CLASS);
    }

    /**
     * @param \minions\model\Group     $group
     * @param \minions\model\GroupUser $model
     * @param                          $offset
     * @param                          $limit
     * @return GroupUser[]|null
     * @throws \Exception
     */
    public function getAllGroupUser(Group $group, GroupUser $model, $offset, $limit){
        if(!$group->id){
            $this->setLastError(Code::FAIL_GROUP_NOT_EXISTS, 'id is empty');
            return null;
        }
        $dbManager = Db::getInstance();
        if($model->checkStatus()){
            $statement = $dbManager->prepare(self::GET_ALL_USERS_WITH_STATUS);
            $statement->bindValue(':gid', $group->id, PDO::PARAM_INT);
            $statement->bindValue(':status', $model->status, PDO::PARAM_INT);
            $statement->bindValue(':offset',$offset, PDO::PARAM_INT);
            $statement->bindValue(':limit',$limit, PDO::PARAM_INT);
        }else{
            $statement = $dbManager->prepare(self::GET_ALL_USERS);
            $statement->bindValue(':gid', $group->id, PDO::PARAM_INT);
            $statement->bindValue(':offset',$offset, PDO::PARAM_INT);
            $statement->bindValue(':limit',$limit, PDO::PARAM_INT);
        }
        if(!$statement->execute()){
            $this->setLastError(Code::FAIL_DATABASE_ERROR,'execute to get group user failed');
            return null;
        }

        return $statement->fetchAll(PDO::FETCH_CLASS, 'minions\model\GroupUser');
    }

    public function getGroupUserCountByGid($gid){

        if(!$gid){
            return false;
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::GET_GROUP_USER_COUNT);
        if(!$statement->execute([$gid])){
            return false;
        }

        return $statement->fetchColumn();
    }

    public function removeAllGroupUser(Group $model){

        if(!$model->id){
            return new ApiResponse(Code::FAIL_GROUP_NOT_EXISTS, 'id is empty');
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::DELETE_ALL_USER);
        if(!$statement->execute([$model->id])){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'delete all group user failed');
        }

        return null;
    }

    public function checkGroupUser(GroupUser $model){

        if(!$model->uid){
            return new ApiResponse(Code::FAIL_USER_NOT_EXISTS, 'uid is empty');
        }
        if(!$model->gid){
            return new ApiResponse(Code::FAIL_GROUP_NOT_EXISTS, 'gid is empty');
        }
        if(!$model->checkStatus()){
            return new ApiResponse(Code::FAIL_GROUP_USER_STATUS, 'status is wrong');
        }
        if(!$model->checkPermission()){
            return new ApiResponse(Code::FAIL_GROUP_USER_PERMISSION, 'permission is wrong');
        }

        return null;
    }

    public function checkGroupWithUser(Group &$group, GroupUser $model){ // object parameter need reference ?

        if(!$group->id){
            return new ApiResponse(Code::FAIL_GROUP_NOT_EXISTS, 'id is empty');
        }
        $model->gid = $group->id;
        if(!$model->uid){
            return new ApiResponse(Code::FAIL_USER_NOT_EXISTS, 'uid is empty');
        }
        $groupManager = GroupManager::getInstance();
        $group = $groupManager->getGroupById($group->id);
        if(!$group){
            return new ApiResponse($groupManager->getLastErrorCode(), null);
        }
        if(!$group->checkType()){
            return new ApiResponse(Code::FAIL_GROUP_TYPE, null);
        }

        return null;
    }

    public function getGroupUserArray(GroupUser $model){

        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::GET_GROUP_USER);
        if(!$statement->execute([$model->gid, $model->uid])){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR,'execute to get group user failed');
        }

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function joinGroup(Group $group, GroupUser $model){

        if($resp = $this->checkGroupWithUser($group, $model)){
            return $resp;
        }
        if( ($result = $this->getGroupUserArray($model)) instanceof ApiResponse){
            return $result;
        }
        if($result){
            if(Group::TYPE_CHATROOM == $group->type){
                return new ApiResponse(Code::FAIL_GROUP_USER_ALREADY_MEMBER, 'you are one of them');
            }else/* if(Group::TYPE_GROUP == $group->type)*/{
                ConvertUtil::arrayToObject($result, $model, ['id', 'gid', 'uid', 'permission', 'create_time', 'status', 'remark']);
                switch($model->status){
                    case GroupUser::STATUS_REQUEST:
                        return new ApiResponse(Code::FAIL_GROUP_USER_ALREADY_REQUEST, 'you have requested');
                    // if user be invited, and want to join this group, make user member
                    case GroupUser::STATUS_INVITED:
                        return null;
                    case GroupUser::STATUS_AGREE:
                        return new ApiResponse(Code::FAIL_GROUP_USER_ALREADY_MEMBER, 'you are one of them');
                    // if i am refuse this guy, but he send again, get he the second chance
                    case GroupUser::STATUS_REFUSED:
                        $model->status = GroupUser::STATUS_REQUEST;
                        if($resp = $this->updateGroupUserStatus($model)){
                            return $resp;
                        }
                        JegarnUtil::sendGroupRequestNotification($group->uid, $model->uid, $group->id, $group->name);
                        return null;
                    default:
                    /*case GroupUser::STATUS_UNSUBSCRIBE:
                    case GroupUser::STATUS_BLACK:*/
                        return new ApiResponse(Code::FAIL_GROUP_USER_ALREADY_REFUSED, 'you have requested');
                }
            }
        }else{
            if(Group::TYPE_CHATROOM == $group->type){
                $model->status = GroupUser::STATUS_AGREE;
                $model->permission = GroupUser::PERMISSION_NORMAL;
                if($resp = $this->addGroupUser($group, $model)){
                    return $resp;
                }
                JegarnUtil::joinChatroom($group->id,$model->uid);
            }else/* if(Group::TYPE_GROUP == $group->type)*/{
                $model->status = GroupUser::STATUS_REQUEST;
                $model->permission = GroupUser::PERMISSION_NORMAL;
                if($resp = $this->addGroupUser($group, $model)){
                    return $resp;
                }
                JegarnUtil::sendGroupRequestNotification($group->uid, $model->uid, $group->id, $group->name);
            }
        }

        return null;
    }

    public function quitGroup(Group $group, GroupUser $model){

        if($resp = $this->checkGroupWithUser($group, $model)){
            return $resp;
        }
        if( ($result = $this->getGroupUserArray($model)) instanceof ApiResponse){
            return $result;
        }
        if($result){
            ConvertUtil::arrayToObject($result, $model, ['id', 'gid', 'uid', 'permission', 'create_time', 'status', 'remark']);
            if(Group::TYPE_CHATROOM == $group->type){
                return GroupManager::getInstance()->quitOrDeleteChatroom($group, $model);
            }else/* if(Group::TYPE_GROUP == $group->type)*/{
                if($group->uid == $model->uid){
                    return new ApiResponse(Code::FAIL_PERMISSION_DENY, 'create user can not quit');
                }
                if($resp = $this->removeGroupUserById($model)){
                    return $resp;
                }
                JegarnUtil::quitGroup($group->id, $model->uid);
                JegarnUtil::sendGroupQuitNotification($group->uid, $model->uid, $group->id, $group->name);
            }
        }
        return null;
    }

    // manager user status and permission
    public function manageUser(GroupUser $model, User $manageUser){

        $checkStatus = $model->checkStatus();
        $checkPermission = $model->checkPermission();
        if(!$checkStatus && !$checkPermission){
            return null;
        }
        if(!($dbModel = $this->getGroupUserByGidUid($model->gid, $model->uid))){
            return new ApiResponse($this->getLastErrorCode(), $this->getLastErrorString());
        }
        $model->id = $dbModel->id;
        // nothing changed as up
        if($checkStatus && $model->status == $dbModel->status && $checkPermission && $model->permission == $dbModel->permission){
            return null;
        }
        $groupManager = GroupManager::getInstance();
        if(!($group = $groupManager->getGroupById($dbModel->gid))){
            return new ApiResponse($groupManager->getLastErrorCode(), null);
        }
        if($group->type == Group::TYPE_CHATROOM){
            return new ApiResponse(Code::FAIL_PERMISSION_DENY, 'chatroom can not manage');
        }
        if(!($userGroupUser = $this->getGroupUserByGidUid($dbModel->gid,$manageUser->id))){
            return new ApiResponse($this->getLastErrorCode(), $this->getLastErrorString());
        }
        if($userGroupUser->permission != GroupUser::PERMISSION_ADMIN && $userGroupUser->permission != GroupUser::PERMISSION_ROOT){
            return new ApiResponse(Code::FAIL_PERMISSION_DENY, 'normal user');
        }
        if($userGroupUser->permission == GroupUser::PERMISSION_ADMIN && ($dbModel->permission == GroupUser::PERMISSION_ADMIN || $dbModel->permission == GroupUser::PERMISSION_ROOT)){
            return new ApiResponse(Code::FAIL_PERMISSION_DENY, 'admin only can manage normal people');
        }
        if($userGroupUser->permission == GroupUser::PERMISSION_ADMIN && $checkPermission){
            return new ApiResponse(Code::FAIL_PERMISSION_DENY, 'admin only can not manage permission');
        }
        if($userGroupUser->permission == GroupUser::PERMISSION_ROOT && $checkPermission && $model->permission == GroupUser::PERMISSION_ROOT){
            return new ApiResponse(Code::FAIL_PERMISSION_DENY, 'always one root');
        }
        if($checkStatus && $checkPermission){
            if($resp = $this->updateGroupUserStatusAndPermission($model)){
                return $resp;
            }
        }else if($checkStatus){
            if($resp = $this->updateGroupUserStatus($model)){
                return $resp;
            }
        }else/* if($checkPermission)*/{
            if($resp = $this->updateGroupUserPermission($model)){
                return $resp;
            }
        }
        $model->gid = $dbModel->gid;
        $model->create_time = $dbModel->create_time;
        $model->uid = $dbModel->uid;
        $model->remark = $dbModel->remark;
        if($dbModel->status != GroupUser::STATUS_AGREE && $model->status == GroupUser::STATUS_AGREE){
            JegarnUtil::joinGroup($model->gid, $model->uid);
            JegarnUtil::sendGroupAgreeNotification($group->uid, $model->uid, $group->id, $group->name);
        }else if($dbModel->status != GroupUser::STATUS_REFUSED && $model->status == GroupUser::STATUS_REFUSED){
            JegarnUtil::sendGroupRefusedNotification($group->uid, $model->uid, $group->id, $group->name);
        }
        return null;
    }
}