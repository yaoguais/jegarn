<?php

namespace jegern\buffer;

class SwooleBuffer implements IBuffer{
    protected $object;
    public function init($size){
        return $this->object = new \swoole_buffer($size);
    }
    public function reSize($size){
        if($this->object){
            return $this->object->expand($size);
        }
        return false;
    }
    public function append(&$data){
        if($this->object){
            return $this->object->append($data);
        }
        return -1;
    }
    public function clear(){
        if($this->object){
            return $this->object->clear();
        }
        return false;
    }
    public function get($offset=0,$length=-1){
        if($this->object){
            return $this->object->substr($offset,$length);
        }
        return null;
    }
    public function destroy(){
        if($this->object){
            unset($this->object);
            return true;
        }
        return false;
    }
}