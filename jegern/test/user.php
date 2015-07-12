<?php

require __DIR__.'/model_init.php';

$userObject = new \jegern\model\UserModel();

$user = [
    'username' => '123@qq.com',
    'password' => '123456',
    'nickname' => 'YaoGuai'
];

if(!($retUser = $userObject->createUser($user))){
    exit("create error\n");
}

if(!($ret = $userObject->getUser($retUser))){
    exit("get user error1\n");
}
echo "created user:\n";
print_r($ret);

$updateUser = [
    'uid' => $retUser['uid'],
    'nickname' => 'doubi',
    'username' => 'aaa'
];

if(!$userObject->updateUser($updateUser)){
    exit("update user error\n");
}

if(!($ret = $userObject->getUser($retUser))){
    exit("get user error2\n");
}
echo "updated user:\n";
print_r($ret);

if(!$userObject->deleteUser($retUser)){
    exit("delete user error\n");
}
echo "after delete\n";
var_dump($userObject->getUser($retUser));