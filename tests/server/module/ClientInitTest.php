<?php

use jegarn\manager\ChatroomManager;
use jegarn\manager\GroupManager;
use jegarn\manager\UserManager;

class ClientInitTest extends PHPUnit_Framework_TestCase {

    /**
     * 1. create three user
     * 2. create a normal group and make three people in
     * 3. create a chatroom, and make three in
     */
    public function testInit(){

        /**
         * @var array $users
         * @var integer $groupId
         * @var integer $chatroomId
         */
        require __DIR__ . '/initData.php';
        $userList = [];
        foreach($users as $user){
            UserManager::getInstance()->addUser($user['uid'], $user['account'], $user['password']);
            $userList[] = $user['uid'];
        }
        // create group, and user to group
        GroupManager::getInstance()->addGroupUsers($groupId, $userList);
        // create chatroom, add user to chatroom
        ChatroomManager::getInstance()->addChatroomUsers($chatroomId, $userList);
    }
}