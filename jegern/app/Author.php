<?php

namespace jegern\app;
use jegern\pack\GluePack;
use jegern\model\UserModel;

class Author extends AppBase {

    public $account;
    public $password;

    public function init(){

    }

    public function toString(){

    }

    public function parse($message){
        GluePack::$glue = "\n";
        $data = GluePack::unpack($message);
        if(isset($data[0])){
            $this->account = $data[0];
        }
        if(isset($data[1])){
            $this->password = $data[1];
        }
    }

    public function login(){
        $model = UserModel::model()->getUser([
           'username' => $this->account,
            'password' => $this->password
        ]);
        if($model){

            return true;
        }else{
            return false;
        }
    }
}