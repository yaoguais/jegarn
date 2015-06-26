<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-23
 * Time: 下午12:34
 */

namespace minions\user;
use minions\auth\IAuth;

abstract class UserBase implements IAuth{
    /**
     * 进行验证操作
     * @return bool
     */
    abstract public function authenticate();
}