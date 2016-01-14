<?php

namespace minions\model;
use minions\util\ConvertUtil;
use minions\util\TextUtil;

class GroupUser extends Base{

    const PERMISSION_NORMAL = 0;
    const PERMISSION_ADMIN = 1;
    const PERMISSION_ROOT = 2;

    const STATUS_REQUEST = 0;
    const STATUS_INVITED = 1;
    const STATUS_UNSUBSCRIBE = 2;
    const STATUS_AGREE = 3;
    const STATUS_REFUSED  = 4;
    const STATUS_BLACK    = 5;

    public $id;
    public $gid;
    public $uid;
    public $permission;
    public $status;
    public $create_time;
    public $remark;

    public function toArray() {

        return ConvertUtil::objectToArray($this,$dst, ['id', 'gid', 'uid', 'status', 'permission', 'create_time', 'remark']);
    }

    public function checkStatus(){

        return !TextUtil::isEmptyString($this->status) && (self::STATUS_REQUEST == $this->status || self::STATUS_INVITED == $this->status
            || self::STATUS_UNSUBSCRIBE == $this->status || self::STATUS_AGREE == $this->status
            || self::STATUS_REFUSED == $this->status || self::STATUS_BLACK == $this->status);
    }

    public function checkPermission(){

        return !TextUtil::isEmptyString($this->permission) && (self::PERMISSION_NORMAL == $this->permission
               || self::PERMISSION_ADMIN == $this->permission || self::PERMISSION_ROOT == $this->permission);
    }
}