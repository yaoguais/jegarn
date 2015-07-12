<?php

namespace jegern\model;
use jegern\cache\CacheManager;

class UserModel extends ModelBase{

    public static $singleInstance = true;

    public static function getConnection(){
        $db = CacheManager::getCache('user');
        $db->useDb(1);
        return $db;
    }

    public function createUser($model){
        $uid = ConfigurationModel::model()->getNextUid();
        $db = $this->getConnection();
        $model = [
            'username' => $model['username'],
            'password' => $this->getEncryptPassword($model['password']),
            'nickname' => $model['nickname'],
            'create_time' => time()
        ];
        if($db->setMap($uid,$model)){
            $model['uid'] = $uid;
            return $model;
        }
        return null;
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