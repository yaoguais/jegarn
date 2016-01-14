<?php

namespace minions\model;
use minions\util\ConvertUtil;
use minions\util\TextUtil;

class Group extends Base{

    const TYPE_GROUP    = 0;
    const TYPE_CHATROOM = 1;

    public $id;
    public $uid;
    public $type;
    public $name;
    public $create_time;
    public $description;
    public $icon;
    public $member_count;

    public function toArray() {
        $result = ConvertUtil::objectToArray($this,$dst, ['id' => 'group_id', 'uid', 'type', 'name', 'create_time', 'description', 'member_count']);
        $result['icon'] = $this->icon ? PIC_HOST . $this->icon : '';
        return $result;
    }

    public function checkType(){
        return !TextUtil::isEmptyString($this->type) && ($this->type == self::TYPE_GROUP || $this->type == self::TYPE_CHATROOM);
    }
}