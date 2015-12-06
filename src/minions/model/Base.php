<?php

namespace minions\model;

abstract class Base {

    const DATA_BASE = 0;
    const DATA_SIMPLE = 1;
    const DATA_MORE = 2;
    const DATA_DETAIL = 3;
    const DATA_FULL = 4;

    abstract function toArray($deep = self::DATA_BASE);
}