<?php

namespace minions\manager;
use minions\util\ConvertUtil;
use minions\model\Group;
use minions\model\GroupUser;
use minions\http\ApiResponse;
use minions\base\Code;
use minions\db\Db;
use minions\model\User;
use minions\util\JegarnUtil;
use minions\util\TextUtil;
use PDO;

class GroupManager extends BaseManager {


    const ADD_GROUP = 'insert into `m_group`(`uid`,`type`,`name`,`create_time`,`description`,`icon`) VALUES(?,?,?,?,?,?)';
    const GET_GROUP =  'select `id`,`uid`,`type`,`name`,`create_time`,`description`,`icon` from `m_group` where id = ?';
    const DELETE_GROUP =  'delete from `m_group` where id = ?';
    const UPDATE_GROUP =  'update `m_group` set uid = ?, `name`=?, description = ? where id = ?';
    const GET_USER_GROUPS = 'select g.`id`,g.`uid`,g.`type`,g.`name`,g.`create_time`,g.`description`,g.`icon` from `m_group` g inner join `m_group_user` u on g.id = u.gid where g.type = ? and u.uid = ? and u.status = ?';
    const GET_HOT_GROUPS = 'SELECT g.`id`,g.`uid`,g.`type`,g.`name`,g.`create_time`,g.`description`,g.`icon`,(select count(*) from m_group_user u where u.gid = g.id and u.status=3 ) as member_count FROM `m_group` g where g.type = ? order by member_count desc limit 20';

    public static function getInstance($class = __CLASS__){
        return parent::getInstance($class);
    }

    public function addGroup(Group $model){

        if($resp = $this->checkGroup($model)){
            return $resp;
        }
        if(!$model->checkType()){
            return new ApiResponse(Code::FAIL_GROUP_TYPE, null);
        }
        $type = $model->type == Group::TYPE_GROUP ? 'g' : 'r';
        $model->icon = 'group/default/' . $type . rand(0, 9) . '.jpg';
        $model->create_time = time();
        $dbManager = Db::getInstance();
        $dbManager->beginTransaction();
        $statement = $dbManager->prepare(self::ADD_GROUP);
        if(!$statement->execute([$model->uid, $model->type, $model->name, $model->create_time, $model->description,$model->icon])){
            $dbManager->rollBack();
            return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'create group failed');
        }
        $model->id = $dbManager->lastInsertId();
        $groupUser = new GroupUser();
        $groupUser->uid = $model->uid;
        $groupUser->status = GroupUser::STATUS_AGREE;
        $groupUser->permission = GroupUser::PERMISSION_ROOT;
        if($resp = GroupUserManager::getInstance()->addGroupUser($model, $groupUser)){
            $dbManager->rollBack();
            return $resp;
        }
        $dbManager->commit();
        if($model->type == Group::TYPE_GROUP){
            JegarnUtil::joinGroup($model->id, $model->uid);
        }else{
            JegarnUtil::joinChatroom($model->id, $model->uid);
        }

        return null;
    }

    public function updateGroup(Group $model){

        if($resp = $this->checkGroup($model)){
            return $resp;
        }
        if(!($dbModel = $this->getGroupById($model->id))){
            return new ApiResponse($this->getLastErrorCode(), null);
        }
        if($model->type != Group::TYPE_CHATROOM && $model->uid != $dbModel->uid){
            return new ApiResponse(Code::FAIL_OBJECT_NOT_FOUND, 'other\'s group');
        }
        $model->type = $dbModel->type;
        $model->create_time = $dbModel->create_time;
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::UPDATE_GROUP);
        if(!$statement->execute([$model->uid, $model->name, $model->description, $model->id])){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'update group failed');
        }

        return null;
    }

    /**
     * 1. if is normal group, delete the group, and make members delete
     * 2. if is chatroom, if only the create user, delete this group, else get first joined user to be 'the create user'
     * @param Group $model
     * @return null|ApiResponse
     */
    public function removeGroup(Group $model){

        if(!$model->uid){
            return new ApiResponse(Code::FAIL_USER_NOT_EXISTS, 'uid is empty');
        }
        if(!($dbModel = $this->getGroupById($model->id))){
            return new ApiResponse($this->getLastErrorCode(), null);
        }
        $model->type = $dbModel->type;
        if($model->uid != $dbModel->uid){
            return new ApiResponse(Code::FAIL_OBJECT_NOT_FOUND, 'other\'s group');
        }
        if(!$model->checkType()){
            return new ApiResponse(Code::FAIL_GROUP_TYPE, null);
        }
        if(Group::TYPE_CHATROOM == $model->type){
            return new ApiResponse(Code::FAIL_PERMISSION_DENY, 'chatroom cannot be delete');
        }else/*(Group::TYPE_GROUP == $model->type)*/{
            JegarnUtil::sendGroupDisbandNotification($dbModel->id, $dbModel->name, $dbModel->uid);
            if($resp = $this->removeGroupCompleted($model)){
                return $resp;
            }
            return null;
        }
    }

    public function quitOrDeleteChatroom(Group $model, GroupUser $groupUser){

        $groupUserManager = GroupUserManager::getInstance();
        $count = $groupUserManager->getGroupUserCountByGid($model->id);
        if($count === false){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'get group user count failed');
        }
        if($count <= 1){
            JegarnUtil::removeChatroom($model->id);
            return $this->removeGroupCompleted($model);
        }else{
            if($resp = $groupUserManager->removeGroupUser($model,$groupUser)){
                return $resp;
            }
            JegarnUtil::quitChatroom($model->id, $groupUser->uid);
            return null;
        }
    }

    public function removeGroupCompleted(Group $model){

        $dbManager =  Db::getInstance();
        $dbManager->beginTransaction();
        $statement = $dbManager->prepare(self::DELETE_GROUP);
        if(!$statement->execute([$model->id])){
            $dbManager->rollBack();
            return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'delete group failed');
        }
        if($resp = GroupUserManager::getInstance()->removeAllGroupUser($model)){
            $dbManager->rollBack();
            return $resp;
        }
        $dbManager->commit();
        JegarnUtil::removeGroup($model->id);

        return null;
    }

    /**
     * @param $id
     * @return Group|null
     * @throws \Exception
     */
    public function getGroupById($id){
        if(!$id){
            $this->setLastError(Code::FAIL_GROUP_NOT_EXISTS, 'id is empty');
            return null;
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::GET_GROUP);
        if(!$statement->execute([$id])){
            $this->setLastError(Code::FAIL_DATABASE_ERROR, 'get group failed');
            return null;
        }
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $statement->setFetchMode(PDO::FETCH_CLASS,'minions\model\Group');

        return $statement->fetch(PDO::FETCH_CLASS);
    }

    public function checkGroup(Group $model){

        if(!$model->uid){
            return new ApiResponse(Code::FAIL_USER_NOT_EXISTS, 'uid is empty');
        }
        if(TextUtil::isEmptyString($model->name)){
            return new ApiResponse(Code::FAIL_GROUP_NAME_EMPTY, null);
        }

        return null;
    }

    /**
     * @param \minions\model\Group     $model
     * @param \minions\model\GroupUser $groupUser
     * @return Group[]|null
     * @throws \Exception
     */
    public function getUserGroups(Group $model, GroupUser $groupUser){
        if(!$groupUser->uid){
            $this->setLastError(Code::FAIL_GROUP_USER_NOT_EXISTS, 'uid is empty');
            return null;
        }
        if(!$model->checkType()){
            $this->setLastError(Code::FAIL_GROUP_TYPE, 'type is error');
            return null;
        }
        if(!$groupUser->checkStatus()){
            $this->setLastError(Code::FAIL_GROUP_USER_STATUS, 'status is error');
            return null;
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::GET_USER_GROUPS);
        if(!$statement->execute([$model->type, $groupUser->uid, $groupUser->status])){
            $this->setLastError(Code::FAIL_DATABASE_ERROR, 'get groups failed');
            return null;
        }

        return $statement->fetchAll(PDO::FETCH_CLASS,'minions\model\Group');
    }

    public function getHotGroups(Group $model){
        if(!$model->checkType()){
            $this->setLastError(Code::FAIL_GROUP_TYPE, 'type is error');
            return null;
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::GET_HOT_GROUPS);
        if(!$statement->execute([$model->type])){
            $this->setLastError(Code::FAIL_DATABASE_ERROR, 'get hot groups failed');
            return null;
        }

        return $statement->fetchAll(PDO::FETCH_CLASS,'minions\model\Group');
    }
}