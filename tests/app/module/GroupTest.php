<?php

use jegarn\packet\GroupAgreeNotification;
use jegarn\packet\GroupQuitNotification;
use jegarn\packet\GroupRefusedNotification;
use jegarn\packet\GroupRequestNotification;
use minions\model\Group;
use minions\model\GroupUser;
use minions\model\User;
use minions\util\ConvertUtil;

class GroupTest extends AppTestBase {

    /**
     * 1. create user A then create group A
     * 2. update the group info
     * 3. create user B, ask into the group, then agree the user request, user B quit the group
     * 4. delete group & created users
     */
    public function testGroup(){

        $userTest = new UserTest();
        $userOne = $userTest->createUser();
        $group = $this->createGroup($userOne, Group::TYPE_GROUP);
        $this->listGroups($group, $userOne, GroupUser::STATUS_AGREE);
        $this->listUsers($group->id,GroupUser::STATUS_AGREE,$userOne);
        $group = $this->updateGroup($userOne, $group);
        $userTwo = $userTest->createUser();
        $groupUser = $this->joinGroup($userTwo, $group);
        $groupUserCopy = clone $groupUser;
        $groupUserCopy->status = GroupUser::STATUS_AGREE;
        $groupUserCopy->permission = GroupUser::PERMISSION_ADMIN;
        $this->managerUser($userOne, $groupUserCopy, $groupUser);
        $this->listGroups($group, $userTwo, GroupUser::STATUS_AGREE);
        $this->listUsers($group->id,GroupUser::STATUS_AGREE,$userTwo);
        $this->quitGroup($userTwo, $group);
        $this->deleteGroup($userOne,$group);
        $userTest->deleteUser($userOne);
        $userTest->deleteUser($userTwo);
    }

    /**
     * 1. create user A then create group A
     * 3. create user B, join the group, user A quit the group
     * 4. delete group & created users
     */
    public function testChatroom(){

        $userTest = new UserTest();
        $userOne = $userTest->createUser();
        $group = $this->createGroup($userOne, Group::TYPE_CHATROOM);
        $this->listGroups($group, $userOne, GroupUser::STATUS_AGREE);
        $this->listUsers($group->id,null,$userOne);
        $group = $this->updateGroup($userOne, $group);
        $userTwo = $userTest->createUser();
        $this->joinGroup($userTwo, $group);
        $this->listGroups($group, $userTwo, GroupUser::STATUS_AGREE);
        $this->listUsers($group->id,null,$userTwo);
        $this->quitGroup($userTwo, $group);
        $this->quitGroup($userOne,$group);
        $userTest->deleteUser($userOne);
        $userTest->deleteUser($userTwo);
    }

    /**
     * @param User $user
     * @param $type
     * @return Group
     */
    public function createGroup(User $user, $type){

        $resp = $this->request('/api/group/create',[
            'uid' => $user->id,
            'token' => $user->token,
            'type' => $type,
            'name' => $type . ' a new name',
            'description' => 'this is a new group'
        ],true);
        $this->assertRequestSuccess($resp);
        $result = $this->getResponseBody($resp);

        return ConvertUtil::arrayToObject($result, new Group(), ['group_id' => 'id', 'uid', 'type', 'create_time', 'name', 'description']);
    }

    public function updateGroup(User $user, Group $group){

        $resp = $this->request('/api/group/update',[
            'uid' => $user->id,
            'token' => $user->token,
            'group_id' => $group->id,
            'name' =>  ' a updated name',
            'description' => 'this is a updated group'
        ],true);
        $this->assertRequestSuccess($resp);
        $result = $this->getResponseBody($resp);

        return ConvertUtil::arrayToObject($result, new Group(), ['group_id' => 'id', 'uid', 'type', 'create_time', 'name', 'description']);
    }

    public function joinGroup(User $user, Group $group){

        $resp = $this->request('/api/group/join',[
            'uid' => $user->id,
            'token' => $user->token,
            'group_id' => $group->id
        ],true);
        $this->assertRequestSuccess($resp);
        $result = $this->getResponseBody($resp);
        // check cache
        if($group->type == Group::TYPE_GROUP){// join group, just a request, not member currently
            $this->assertNotificationPacket($group->uid, new GroupRequestNotification());
        }else{
            self::assertTrue(\jegarn\manager\ChatroomManager::getInstance()->isGroupUser($group->id, $user->id) != false);
        }

        return ConvertUtil::arrayToObject($result, new GroupUser(), ['id', 'gid', 'uid', 'status', 'permission', 'create_time', 'remark']);
    }

    public function quitGroup(User $user, Group $group){

        $resp = $this->request('/api/group/quit',[
            'uid' => $user->id,
            'token' => $user->token,
            'group_id' => $group->id
        ],true);
        $this->assertRequestSuccess($resp);
        $result = $this->getResponseBody($resp);
        // check cache
        if($group->type == Group::TYPE_GROUP){
            self::assertTrue(\jegarn\manager\GroupManager::getInstance()->isGroupUser($group->id, $user->id) == false);
            $this->assertNotificationPacket($group->uid, new GroupQuitNotification());
        }else{
            self::assertTrue(\jegarn\manager\ChatroomManager::getInstance()->isGroupUser($group->id, $user->id) == false);
        }

        return ConvertUtil::arrayToObject($result, new GroupUser(), ['id', 'gid', 'uid', 'status', 'permission', 'create_time', 'remark']);
    }

    public function deleteGroup(User $user,Group $group){

        $resp = $this->request('/api/group/delete',[
            'uid' => $user->id,
            'token' => $user->token,
            'group_id' => $group->id
        ],true);
        $this->assertRequestSuccess($resp);
        // check cache

    }

    public function managerUser(User $manager, GroupUser $model, GroupUser $previousGroupUser){

        $resp = $this->request('/api/group/manage_user',[
            'uid' => $manager->id,
            'token' => $manager->token,
            'gid' => $previousGroupUser->gid,
            'user_id' => $previousGroupUser->uid,
            'status' =>  $model->status,
            'permission' => $model->permission
        ],true);
        $this->assertRequestSuccess($resp);
        $result = $this->getResponseBody($resp);
        if($model->status != $previousGroupUser->status){
            if($model->status == GroupUser::STATUS_AGREE){
                self::assertTrue(\jegarn\manager\GroupManager::getInstance()->isGroupUser($previousGroupUser->gid, $previousGroupUser->uid) != false);
                $this->assertNotificationPacket($previousGroupUser->uid, new GroupAgreeNotification());
            }else if($model->status == GroupUser::STATUS_REFUSED){
                $this->assertNotificationPacket($previousGroupUser->uid, new GroupRefusedNotification());
            }
        }

        return ConvertUtil::arrayToObject($result, new GroupUser(), ['id', 'gid', 'uid', 'status', 'permission', 'create_time', 'remark']);
    }

    public function listGroups(Group $group, User $user, $status){
        $resp = $this->request('/api/group/list',[
            'uid' => $user->id,
            'token' => $user->token,
            'type' => $group->type,
            'status' => $status
        ]);
        $this->assertResponseNotEmptyList($resp);
        $results = $this->getResponseBody($resp);
        $objList = [];
        foreach($results as $result){
            $objList[] = ConvertUtil::arrayToObject($result, new Group(), ['group_id' => 'id', 'uid', 'type', 'create_time', 'name', 'description']);
        }
        return $objList;
    }

    public function listUsers($groupId, $status, User $user){
        $resp = $this->request('/api/group/list_user',[
            'uid' => $user->id,
            'token' => $user->token,
            'group_id' => $groupId,
            'status' => $status
        ]);
        $this->assertResponseNotEmptyList($resp);
        $results = $this->getResponseBody($resp);
        $objList = [];
        foreach($results as $result){
            $objList[] = ConvertUtil::arrayToObject($result, new GroupUser(), ['id', 'gid', 'uid', 'status', 'permission', 'create_time', 'remark']);
        }
        return $objList;
    }
}