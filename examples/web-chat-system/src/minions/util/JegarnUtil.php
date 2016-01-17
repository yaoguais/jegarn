<?php

namespace minions\util;
use jegarn\manager\ChatroomManager;
use jegarn\manager\GroupManager;
use jegarn\manager\UserManager;
use jegarn\packet\FriendAgreeNotification;
use jegarn\packet\FriendRefusedNotification;
use jegarn\packet\FriendRequestNotification;
use jegarn\packet\GroupAgreeNotification;
use jegarn\packet\GroupDisbandNotification;
use jegarn\packet\GroupInvitedNotification;
use jegarn\packet\GroupQuitNotification;
use jegarn\packet\GroupRefusedNotification;
use jegarn\packet\GroupRequestNotification;
use jegarn\packet\TextChat;
use jegarn\server\Server;
use minions\model\User;

abstract class JegarnUtil{

    public static function addUser($uid, $account, $token){
        UserManager::getInstance()->addUser(intval($uid), $account, $token);
    }

    public static function updateUser($uid, $account, $token){
        UserManager::getInstance()->addUser(intval($uid), $account, $token);
    }

    public static function removeUser($account){
        UserManager::getInstance()->removeUser($account);
    }

    public static function sendGroupRequestNotification($hostId, $requestUid, $groupId, $groupName){
        $packet = new GroupRequestNotification();
        $packet->to = intval($hostId);
        $packet->setUserId(intval($requestUid));
        $packet->setGroupId(intval($groupId));
        $packet->setGroupName($groupName);
        Server::getInstance()->sendPacket($packet);
    }

    public static function sendGroupRefusedNotification($hostId, $requestUid, $groupId, $groupName){
        $packet = new GroupRefusedNotification();
        $packet->to = intval($requestUid);
        $packet->setUserId(intval($hostId));
        $packet->setGroupId(intval($groupId));
        $packet->setGroupName($groupName);
        Server::getInstance()->sendPacket($packet);
    }

    public static function sendGroupInvitedNotification($hostId, $requestUid, $groupId, $groupName){
        $packet = new GroupInvitedNotification();
        $packet->to = intval($requestUid);
        $packet->setUserId(intval($hostId));
        $packet->setGroupId(intval($groupId));
        $packet->setGroupName($groupName);
        Server::getInstance()->sendPacket($packet);
    }

    public static function joinGroup($groupId, $requestUid){
        GroupManager::getInstance()->addGroupUser(intval($groupId), intval($requestUid));
    }

    public static function joinChatroom($groupId, $requestUid){
        ChatroomManager::getInstance()->addChatroomUser(intval($groupId), intval($requestUid));
    }

    public static function sendGroupAgreeNotification($hostId, $requestUid, $groupId, $groupName){
        $packet = new GroupAgreeNotification();
        $packet->to = intval($requestUid);
        $packet->setUserId(intval($hostId));
        $packet->setGroupId(intval($groupId));
        $packet->setGroupName($groupName);
        Server::getInstance()->sendPacket($packet);
    }

    public static function removeGroup($groupId){
        GroupManager::getInstance()->removeGroup(intval($groupId));
    }

    public static function removeChatroom($groupId){
        ChatroomManager::getInstance()->removeChatroom(intval($groupId));
    }

    public static function sendGroupDisbandNotification($groupId, $groupName, $hostId){
        $groupId = intval($groupId);
        $hostId = intval($hostId);
        if($groupUidList = GroupManager::getInstance()->getGroupUsers($groupId)){
            $packet = new GroupDisbandNotification();
            $packet->setUserId($hostId);
            $packet->setGroupId($groupId);
            $packet->setGroupName($groupName);
            foreach($groupUidList as $requestUid){
                if($requestUid != $hostId){
                    $packet->to = $requestUid;
                    Server::getInstance()->sendPacket($packet);
                }
            }
        }
    }

    public static function quitGroup($groupId, $requestUid){
        GroupManager::getInstance()->removeGroupUser(intval($groupId), intval($requestUid));
    }

    public static function quitChatroom($groupId, $requestUid){
        ChatroomManager::getInstance()->removeChatroomUser(intval($groupId), intval($requestUid));
    }

    public static function sendGroupQuitNotification($hostId, $requestUid, $groupId, $groupName){
        $packet = new GroupQuitNotification();
        $packet->to = intval($hostId);
        $packet->setUserId(intval($requestUid));
        $packet->setGroupId(intval($groupId));
        $packet->setGroupName($groupName);
        Server::getInstance()->sendPacket($packet);
    }

    public static function sendFriendRequestNotification($requesterUid, $targetUid){
        $packet = new FriendRequestNotification();
        $packet->to = $targetUid;
        $packet->setUserId($requesterUid);
        Server::getInstance()->sendPacket($packet);
    }

    public static function sendFriendAgreeNotification($requesterUid, $targetUid){
        $packet = new FriendAgreeNotification();
        $packet->to = intval($requesterUid);
        $packet->setUserId(intval($targetUid));
        Server::getInstance()->sendPacket($packet);
    }

    public static function sendFriendRefusedNotification($requesterUid, $targetUid){
        $packet = new FriendRefusedNotification();
        $packet->to = intval($requesterUid);
        $packet->setUserId(intval($targetUid));
        Server::getInstance()->sendPacket($packet);
    }

    public static function sendUserSystemTextChatMessage($to, $text){
        $packet = new TextChat();
        $packet->setFromSystemUser();
        $packet->to = intval($to);
        $packet->setText($text);
        Server::getInstance()->sendPacket($packet);
    }

    public static function getUserPresent($uid){
        $ret = UserManager::getInstance()->isUserOnline(intval($uid));
        return $ret ? User::ONLINE : User::OFFLINE;
    }
}