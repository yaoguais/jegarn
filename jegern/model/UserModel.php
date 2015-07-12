<?php

namespace jegern\model;
use jegern\cache\CacheManager;
use jegern\cache\ICache;

class UserModel extends ModelBase{

    public static $singleInstance = true;

    public static function getConnection(){
        $db = CacheManager::getCache('user');
        $db->useDb(1);
        return $db;
    }

    public function createUser($model){
        $db = $this->getConnection();
        $uid = ConfigurationModel::model()->getNextUid();
        return $db->setMap($uid,[
            'username' => $model['username'],
            'password' => $this->getEncryptPassword($model['password']),
            'nickname' => $model['nickname'],
            'create_time' => time()
        ]);
    }

    public function getEncryptPassword($password){
        return hash('sha256',$password);
    }

    public function deleteUser($model){
        $db = $this->getConnection();
        return $db->deleteMap($model['uid']);
    }

    public function updateUser($model){
        $db = $this->getConnection();
        $uid = $model['uid'];
        unset($model['uid']);
        return $db->setMap($uid,$model);
    }

    public function getUser($model){
        $db = $this->getConnection();
        return $db->getMap($model['uid']);
    }

}