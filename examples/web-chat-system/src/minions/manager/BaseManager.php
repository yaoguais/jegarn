<?php
/**
 * model manager coding rules:
 * 1. add model method, if success return new model, else return false
 * 2. update model method, method name must contain primary key,
 *    if success return modified model, else return false
 * 3. delete model method, method name must contain primary key,
 *    if delete success, return previous model, else return false
 * 4. get model method, method name must contain primary key,
 *    if success return one model or list of model, else return null
 * 5. all method, if something is wrong, set last code & string
 */
namespace minions\manager;
use minions\base\Singleton;

class BaseManager extends Singleton {

    protected $lastErrorCode;
    protected $lastErrorString;

    public function getLastErrorCode() {
        return $this->lastErrorCode;
    }

    public function getLastErrorString(){
        return $this->lastErrorString;
    }

    protected function setLastError($code, $message) {
        $this->lastErrorCode = $code;
        $this->lastErrorString = $message;
    }
}