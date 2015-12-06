<?php

use minions\manager\UserManager;
use minions\model\User;
use minions\util\Convert;

class UserTest extends AppTestBase {

    public function testUser(){

        // create a user
        $resp = $this->request('/api/user/create',[
            'account' => 'test_'.rand(1000,9999),
            'password' => '123456',
            'nick' => '测试'
        ],true, [
            'avatar' => $this->getFile(self::IMAGE)
        ]);
        $this->assertRequestSuccess($resp);
        // update this user
        if($user = $this->getResponseBody($resp)){
            $updateResp = $this->request('/api/user/update',[
                'uid' => $user['uid'],
                'token' => $user['token'],
                'nick' => '测试2'
            ],true,[
                'avatar' => $this->getFile(self::IMAGE)
            ]);
            $this->assertRequestSuccess($updateResp);
            // login
            $loginResp = $this->request('/api/user/login',[
                'account' => $user['account'],
                'password' => '123456'
            ],true);
            $this->assertRequestSuccess($loginResp);
        }
        // delete this user
        if($user = $this->getResponseBody($resp)){
            $model = new User();
            Convert::arrayToObject($user,$model,['uid' => 'id', 'account' => 'username', 'nick', 'token']);
            self::assertTrue(null === UserManager::getInstance()->removeUser($model));
        }
    }
}