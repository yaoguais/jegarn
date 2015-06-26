<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 上午7:50
 */

namespace minions\event;

class Event{
    /**
     * 事件的发出着
     * @var
     */
    public $target;

    /**
     * 时间类型
     * @var
     */
    public $name;

    /**
     * 时间回调
     * @var
     */
    public $callback;

    /**
     * 是否永久事件
     * @var
     */
    public $persistent;

    /**
     * @param $target
     * @param $name
     * @param $callback
     * @param bool $persistent
     * @throws EventException
     */
    public function __construct(&$target,$name,$callback,$persistent){
        $this->target = & $target;
        $this->name = $name;
        $this->callback = $callback;
        $this->persistent = $persistent;
    }
}