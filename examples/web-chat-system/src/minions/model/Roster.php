<?php

namespace minions\model;

use minions\util\ConvertUtil;
use minions\util\TextUtil;

class Roster extends Base {

    const STATUS_REQUEST = 0;
    const STATUS_RECEIVE = 1;
    const STATUS_UNSUBSCRIBE = 2;
    const STATUS_AGREE = 3;
    const STATUS_REFUSED  = 4;
    const STATUS_BLACK    = 5;

    public $id;
    public $uid;
    public $target_id;
    public $status;
    public $create_time;
    public $update_time;
    public $remark;
    public $group_id;
    public $rank;

    public function checkStatus(){

        return !TextUtil::isEmptyString($this->status) && (self::STATUS_REQUEST == $this->status || self::STATUS_RECEIVE == $this->status
            || self::STATUS_UNSUBSCRIBE == $this->status || self::STATUS_AGREE == $this->status
            || self::STATUS_REFUSED == $this->status || self::STATUS_BLACK == $this->status);
    }

    function toArray() {

        return ConvertUtil::objectToArray($this,$dst, ['id' => 'roster_id', 'uid', 'target_id', 'status', 'create_time', 'update_time', 'remark', 'group_id', 'rank']);
    }
}