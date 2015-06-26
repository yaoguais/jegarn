<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-23
 * Time: 下午10:39
 */

namespace minions\user;

class User extends UserBase{

    public $uid;
    public $username;
    public $password;
    public $nickname;
    public $fd;

    public function authenticate(){

    }
}