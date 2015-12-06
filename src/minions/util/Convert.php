<?php

namespace minions\util;

abstract class Convert {

    public static function arrayToObject($arr, $dst, $field = null){

        if(null === $field){
            foreach($arr as $k=>$v){
                $dst->$k = $v;
            }
        }else{
            foreach($field as $k=>$v){
                $dst->$v = is_string($k) ? $arr[$k] : $arr[$v];
            }
        }

        return $dst;
    }

    public static function objectToArray($obj, &$dst, $field = null){

        if($field === null){
            $dst = (array)$obj;
        }else{
            foreach($field as $k=>$v){
                $dst[$v] = is_string($k) ? $obj->$k : $obj->$v;
            }
        }

        return $dst;
    }
}