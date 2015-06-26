<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 上午7:54
 */

namespace minions\event;

interface IEventManager {
    public function addEvent(Event &$event);
    public function removeEvent(Event &$event);
    public function hasEvent(Event &$event);
    public function attachEvent(Event &$event,$attachFunc);
    public function dispatchEvent($name);
}