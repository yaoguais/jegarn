<?php

namespace minions\model;

class LoginLog extends Base {

    const STATUS_FAILED = 0;
    const STATUS_SUCCESS = 1;

    public $id;
    public $uid;
    public $createTime;
    public $ip;
    public $status;

    public function __construct($uid, $status) {

        $this->uid = $uid;
        $this->status = $status;
    }

    public function toArray() {
        return null;
    }
}