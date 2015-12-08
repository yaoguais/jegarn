<?php

namespace minions\model;

class GroupRoster extends Base {

    const DEFAULT_NAME = '默认分组';
    public $id;
    public $uid;
    public $name;
    public $rank;

    function toArray($deep = self::DATA_BASE) {

        return [];
    }
}