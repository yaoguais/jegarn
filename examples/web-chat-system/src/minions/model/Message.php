<?php

namespace minions\model;
use minions\util\ConvertUtil;

class Message extends Base{

    public $id;
    public $from;
    public $to;
    public $create_time;
    public $message;

    public function toArray() {

        return ConvertUtil::objectToArray($this,$dst, ['id', 'from', 'to', 'create_time', 'message']);
    }
}