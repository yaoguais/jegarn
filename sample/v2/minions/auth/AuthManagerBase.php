<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-23
 * Time: 下午12:33
 */

namespace minions\auth;
use minions\base\SingleInstanceBase;

abstract class AuthManagerBase extends SingleInstanceBase{

    private $_model;

    /**
     * 设置需要验证的模型
     * @param $model
     */
    public function setModel(IAuth $model){
        $this->_model = $model;
    }

    /**
     * 进行验证
     * @return bool
     */
    public function authenticate(){
        return $this->_model->authenticate();
    }
}