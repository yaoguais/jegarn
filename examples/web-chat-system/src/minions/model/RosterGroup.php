<?php

namespace minions\model;

use minions\util\ConvertUtil;
use minions\util\TextUtil;

class RosterGroup extends Base {

    const DEFAULT_NAME = 'default';
    public $id;
    public $uid;
    public $name;
    public $rank;

    function toArray() {

        $dst = ConvertUtil::objectToArray($this,$dst, ['id' => 'group_id', 'uid', 'rank']);
        $dst['name'] = $this->isDefaultGroup() ? self::DEFAULT_NAME : $this->name;
        return $dst;
    }

    public function isDefaultGroup(){
        return intval($this->id) == 0;
    }
}