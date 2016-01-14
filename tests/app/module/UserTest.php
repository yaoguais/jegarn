<?php

use minions\manager\UserManager;
use minions\model\User;
use minions\util\ConvertUtil;

class UserTest extends AppTestBase {

    /**
     * 1. create a user
     * 2. update this user
     * 3. make this user login
     * 4. delete this user
     */
    public function testUser(){
        // create a user
        $user = $this->createUser();
        // update this user
        $updateResp = $this->request('/api/user/update',[
            'uid' => $user->id,
            'token' => $user->token,
            'nick' => '测试2'
        ],true,[
            'avatar' => $this->getFile(self::IMAGE)
        ]);
        $this->assertRequestSuccess($updateResp);
        // login
        $loginResp = $this->request('/api/user/login',[
            'account' => $user->username,
            'password' => '123456'
        ],true);
        $this->assertRequestSuccess($loginResp);
        // delete this user
        $this->deleteUser($user);
    }

    public function createUser(){

        // create a user
        $account = 'test_'.rand(0,99999999);
        $resp = $this->request('/api/user/create',[
            'account' => $account,
            'password' => '123456',
            'nick' => '测试',
            'motto' => 'do what you want'
        ],true, [
            'avatar' => $this->getFile(self::IMAGE)
        ]);
        $this->assertRequestSuccess($resp);
        $user = $this->getResponseBody($resp);
        // cache check, user should be register into cache database
        self::assertTrue(\jegarn\manager\UserManager::getInstance()->getUser($account) !== null);

        return ConvertUtil::arrayToObject($user, new User(), ['uid' => 'id', 'account' => 'username', 'nick', 'token']);
    }

    public function createUserByAccount($account, $password, $nick){

        // create a user
        $resp = $this->request('/api/user/create',[
            'account' => $account,
            'password' => $password,
            'nick' => $nick
        ],true);
        $this->assertRequestSuccess($resp);
        $user = $this->getResponseBody($resp);
        // cache check, user should be register into cache database
        self::assertTrue(\jegarn\manager\UserManager::getInstance()->getUser($account) !== null);

        return ConvertUtil::arrayToObject($user, new User(), ['uid' => 'id', 'account' => 'username', 'nick', 'token']);
    }

    public function getUserByAccount($account){
        $user = new User();
        $user->username = $account;
        if(!UserManager::getInstance()->getUser($user)){
            return $user;
        }else{
            return null;
        }
    }

    public function deleteUser(User $user){

        self::assertTrue(null === UserManager::getInstance()->removeUser($user));
        // user should be remove from cache
        self::assertTrue(\jegarn\manager\UserManager::getInstance()->getUser($user->username) === null);
    }
}