<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-23
 * Time: 下午12:33
 */

namespace minions\auth;

interface IAuth{
    public function authenticate();
}