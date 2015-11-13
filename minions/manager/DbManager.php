<?php

namespace minions\manager;
use \Exception;

class DbManager extends BaseManager {

    /* @var \PDO */
    protected $_pdo;

    /**
     * @param null|string $class
     *
     * @return static
     * @throws Exception
     */
    public static function getInstance($class = __CLASS__){

        return parent::getInstance($class);
    }

    public function __call($name, $arguments){

        if(!method_exists($this->_pdo, $name)){
            throw new Exception('method not exits');
        }

        return call_user_func_array([$this->_pdo, $name], $arguments);
    }
}