<?php

namespace minions\model;
use minions\util\Convert;

class User extends Base{

    public $id;
    public $username;
    public $password;
    public $nick;
    public $avatar;
    public $createTime;
    public $token;
    public $regIp;

    public function toArray($deep = self::DATA_BASE) {

        return Convert::objectToArray($this,$dst, ['id' => 'uid', 'username' => 'account', 'nick', 'token', 'avatar']);
    }
}