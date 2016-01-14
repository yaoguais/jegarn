<?php

namespace jegarn\manager;

use jegarn\cache\Cache;

class ChatroomManager extends BaseManager{

    public static function getInstance($class = __CLASS__){
        return parent::getInstance($class);
    }

    /**
     * @param $rid
     * @return bool
     */
    public function addChatroom(/** @noinspection PhpUnusedParameterInspection */
        $rid){
        return true;
    }

    /**
     * @param $rid
     * @return bool
     * @throws \Exception
     */
    public function removeChatroom($rid){
        return Cache::getInstance()->del($this->getCacheKey($rid)) > 0;
    }

    /**
     * return the user number of add into chatroom
     * @param integer        $rid
     * @param integer|string $uid
     * @return int
     * @throws \Exception
     */
    public function addChatroomUser($rid, $uid){
        return Cache::getInstance()->sAdd($this->getCacheKey($rid), $uid);
    }

    /**
     * check user is a member of this chatroom
     * @param                $rid
     * @param integer|string $uid
     * @return bool
     * @throws \Exception
     */
    public function isGroupUser($rid, $uid){
        return Cache::getInstance()->sIsMember($this->getCacheKey($rid), $uid);
    }

    /**
     * return the user number of add into chatroom
     * @param integer $rid
     * @param array   $uidList
     * @return int
     * @throws \Exception
     */
    public function addChatroomUsers($rid, $uidList){
        array_unshift($uidList, $this->getCacheKey($rid));
        return call_user_func_array([Cache::getInstance(), 'sAdd'], $uidList);
    }

    /**
     * get all user of chatroom
     * @param $gid
     * @return array
     * @throws \Exception
     */
    public function getGroupUsers($rid){
        return Cache::getInstance()->sMembers($this->getCacheKey($rid));
    }

    /**
     * return the user number of remove out of chatroom
     * @param integer        $rid
     * @param integer|string $uid
     * @return int
     * @throws \Exception
     */
    public function removeChatroomUser($rid, $uid){
        return Cache::getInstance()->sRem($this->getCacheKey($rid), $uid);
    }

    /**
     * return the user number of remove out of chatroom
     * @param integer $rid
     * @param array   $uidList
     * @throws \Exception
     */
    public function removeChatroomUsers($rid, $uidList){
        array_unshift($uidList, $this->getCacheKey($rid));
        call_user_func_array([Cache::getInstance(), 'sRem'], $uidList);
    }

    protected function getCacheKey($id){
        return 'R_' . $id;
    }
}