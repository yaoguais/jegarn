<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-23
 * Time: 下午12:37
 */

namespace minions\user;

interface IUserManager{
    /**
     * 添加用户
     * @param UserBase $user
     * @return bool
     */
    public function addUser(UserBase $user);

    /**
     * 删除一个用户
     * @param UserBase $user
     * @return bool
     */
    public function removeUser(UserBase $user);

    /**
     * 判断用户是否存在
     * @param UserBase $user
     * @return UserBase/null
     */
    public function getUser(UserBase $user);
}