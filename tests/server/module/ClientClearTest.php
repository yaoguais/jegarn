<?php


use jegarn\manager\ChatroomManager;
use jegarn\manager\GroupManager;
use jegarn\manager\UserManager;

class ClientClearTest extends PHPUnit_Framework_TestCase {

    public function testClear(){

        /**
         * @var array $users
         * @var integer $groupId
         * @var integer $chatroomId
         */
        require __DIR__ . '/initData.php';
        $userList = [];
        foreach($users as $user){
            UserManager::getInstance()->removeUser($user['account']);
            $userList[] = $user['uid'];
        }
        // create group, and user to group
        GroupManager::getInstance()->removeGroupUsers($groupId, $userList);
        // create chatroom, add user to chatroom
        ChatroomManager::getInstance()->removeChatroomUsers($chatroomId, $userList);
    }
}