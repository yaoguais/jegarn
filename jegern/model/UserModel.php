<?php

namespace jegern\model;
use jegern\cache\CacheManager;

class UserModel extends ModelBase{

    public static $singleInstance = true;

    public function getConnection(){
        $db = CacheManager::getCache('user');
        $db->useDb(1);
        return $db;
    }

    public function createUser($model){
        $uid = ConfigurationModel::model()->getNextUid();
        $model = [
            'username' => $model['username'],
            'password' => $this->getEncryptPassword($model['password']),
            'nickname' => $model['nickname'],
            'create_time' => time()
        ];
        if($this->getConnection()->setMap($uid,$model)){
            $model['uid'] = $uid;
            return $model;
        }
        return null;
    }

    public function getEncryptPassword($password){
        return hash('sha256',$password);
    }

    public function deleteUser($model){
        return $this->getConnection()->deleteMap($model['uid']);
    }

    public function updateUser($model){
        $uid = $model['uid'];
        unset($model['uid']);
        return $this->getConnection()->setMap($uid,$model);
    }

    public function getUser($model){
        return $this->getConnection()->getMap($model['uid']);
    }

    public static function pack($uid){
        return pack('N',$uid);
    }

    public static function unpack($uidPacked){
        return unpack('N',$uidPacked);
    }

}