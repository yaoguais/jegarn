<?php

namespace minions\model;
use minions\util\ConvertUtil;
use minions\util\JegarnUtil;

class User extends Base{

    public $id;
    public $username;
    public $password;
    public $nick;
    public $avatar;
    public $motto;
    public $create_time;
    public $token;
    public $reg_ip;

    const OFFLINE = 0;
    const ONLINE = 1;

    public function toArray() {
        $result = ConvertUtil::objectToArray($this,$dst, ['id' => 'uid', 'username' => 'account', 'nick', 'motto', 'token']);
        $result['avatar'] = $this->avatar ? PIC_HOST . $this->avatar : null;
        $result['present'] = JegarnUtil::getUserPresent($this->id);
        return $result;
    }

    public function makeSecret(){
        $this->password = $this->token = $this->reg_ip = null;
    }
}