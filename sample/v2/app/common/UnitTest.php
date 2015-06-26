<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-24
 * Time: 下午11:05
 */

class User{
    public $username;
    public $password;
    public function __construct($username,$password){
        $this->username = $username;
        $this->password = $password;
    }
}

class A{

    protected static $instance;
    private function __construct(){}
    public static function getInstance(){
        if(empty(self::$instance)){
            self::$instance = new A;
        }
        return self::$instance;
    }

    private $_users;
    public function addUser(User $user){
        if(count($this->_users)>5){
            return;
        }
        $this->_users[] = $user;
    }

    public function listUser(){
        foreach($this->_users as $i=>&$user){
            echo "$i:-------------\n";
            print_r($this->_users);
            $this->addUser(new User('test','123456'));
        }
    }
}

A::getInstance()->addUser(new User('demo','123456'));
A::getInstance()->listUser();