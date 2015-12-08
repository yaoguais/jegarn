<?php

namespace minions\model;

class Roster extends Base {

    const STATUS_ASK = 0;
    const STATUS_RECEIVED = 1;
    const STATUS_REFUSED  = 2;

    public $id;
    public $uid;
    public $friendId;
    public $status;
    public $createTime;
    public $updateTime;
    public $remark;
    public $groupId;
    public $rank;

    public function __construct($uid, $friendId,$status, $remark,$groupId,$rank){

        $this->uid = $uid;
        $this->friendId = $friendId;
        $this->status = $status != self::STATUS_ASK && $status != self::STATUS_RECEIVED && $status != self::STATUS_REFUSED ? self::STATUS_ASK : $status;
        $this->remark = $remark;
        $this->groupId = $groupId;
        $this->rank = $rank;
        $this->createTime = time();
    }

    function toArray($deep = self::DATA_BASE) {

        return [];
    }
}