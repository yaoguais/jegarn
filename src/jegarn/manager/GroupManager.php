<?php

namespace jegarn\manager;

use jegarn\cache\Cache;

class GroupManager extends BaseManager{

    public static function getInstance($class = __CLASS__){
        return parent::getInstance($class);
    }

    /**
     * @param $gid
     * @return bool
     */
    public function addGroup(/** @noinspection PhpUnusedParameterInspection */$gid){
        return true;
    }

    /**
     * @param $gid
     * @return bool
     * @throws \Exception
     */
    public function removeGroup($gid){
        return Cache::getInstance()->del($this->getCacheKey($gid)) > 0;
    }

    /**
     * return the user number of add into group
     * @param integer        $gid
     * @param integer|string $uid
     * @return int
     * @throws \Exception
     */
    public function addGroupUser($gid, $uid){
        return Cache::getInstance()->sAdd($this->getCacheKey($gid), $uid);
    }

    /**
     * check user is a member of this group
     * @param                $gid
     * @param integer|string $uid
     * @return bool
     * @throws \Exception
     */
    public function isGroupUser($gid, $uid){
        return Cache::getInstance()->sIsMember($this->getCacheKey($gid), $uid);
    }

    /**
     * return the user number of add into group
     * @param integer $gid
     * @param array   $uidList
     * @return int
     * @throws \Exception
     */
    public function addGroupUsers($gid, $uidList){
        array_unshift($uidList, $this->getCacheKey($gid));
        return call_user_func_array([Cache::getInstance(), 'sAdd'], $uidList);
    }

    /**
     * get all user of group
     * @param $gid
     * @return array
     * @throws \Exception
     */
    public function getGroupUsers($gid){
        return Cache::getInstance()->sMembers($this->getCacheKey($gid));
    }

    /**
     * return the user number of remove out of group
     * @param integer        $gid
     * @param integer|string $uid
     * @return int
     * @throws \Exception
     */
    public function removeGroupUser($gid, $uid){
        return Cache::getInstance()->sRem($this->getCacheKey($gid), $uid);
    }

    /**
     * return the user number of remove out of group
     * @param integer $gid
     * @param array   $uidList
     * @throws \Exception
     */
    public function removeGroupUsers($gid, $uidList){
        array_unshift($uidList, $this->getCacheKey($gid));
        call_user_func_array([Cache::getInstance(), 'sRem'], $uidList);
    }

    protected function getCacheKey($id){
        return 'G_' . $id;
    }
}