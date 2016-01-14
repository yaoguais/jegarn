<?php

namespace jegarn\manager;

use jegarn\cache\Cache;
use jegarn\util\ConvertUtil;

class UserManager extends BaseManager{

    public static function getInstance($class = __CLASS__){
        return parent::getInstance($class);
    }

    /**
     * account and password for user auth
     * when your application has no uid, you can set uid same as account
     * notice: uid and account must be unique
     * @param integer|string $uid
     * @param string         $account
     * @param string         $password
     * @throws \Exception
     */
    public function addUser($uid, $account, $password){
        Cache::getInstance()->set($this->getCacheKey($account), ConvertUtil::pack(['uid' => $uid, 'account' => $account, 'password' => $this->encryptPassword($password)]));
    }

    public function encryptPassword($password){
        return hash('sha256', $password);
    }

    public function authPassword($input, $cryptPassword){
        return $input && $this->encryptPassword($input) == $cryptPassword;
    }

    public function getUser($account){
        if($str = Cache::getInstance()->get($this->getCacheKey($account))){
            return ConvertUtil::unpack($str);
        }
        return null;
    }

    public function removeUser($account){
        Cache::getInstance()->del($this->getCacheKey($account));
    }

    protected function getCacheKey($id){
        return 'U_' . $id;
    }
}