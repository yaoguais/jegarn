<?php

namespace minions\base;
/**
 * Class Cacheable
 * @package minions\base
 * @property string $id
 * @property bool   $isEncode
 */
abstract class Cacheable {

    protected $id;
    protected $isEncode;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function isEncode() {
        return $this->isEncode;
    }

    public function setEncode($encode) {
        $this->isEncode = $encode;
        return $this;
    }

    public function encode(){

    }

    public function decode(){

    }
}