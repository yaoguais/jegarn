<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-23
 * Time: 上午9:34
 */

/**
 * minions:
 * 基于php扩展swoole 1.7.17
 * 1.用户验证模块
 * 2.基于事件驱动
 * 3.通讯协议
 * 4.应用模块
 * 5.用户管理
 * 6.模块插拔化：通讯协议、应用模块、用户管理
 */

interface IAuth{
    public function authenticate();
}

abstract class AuthManagerBase{

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

abstract class UserBase implements IAuth{
    /**
     * 进行验证操作
     * @return bool
     */
    abstract public function authenticate();
}

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

abstract class MessageBase{

}

interface IMessageManager{
    const SEND_SUCCESS = 1;
    const SEND_ERROR = 2;
    const SEND_TO_CACHE = 3;

    /**
     * 给指定发送一条消息,可能会是发送的离线消息
     * @param UserBase $fromUser
     * @param UserBase $toUser
     * @param MessageBase $message
     * @return send_status:1,2,3
     */
    public function sendMessage(UserBase $fromUser,UserBase $toUser,MessageBase $message);

    /**
     * 发送广播消息,发送之前做用户检测
     * @param UserBase $fromUser
     * @param list<UserBase> $toUsers
     * @param MessageBase $message
     * @return bool
     */
    public function broadcast(UserBase $fromUser,$toUsers,MessageBase $message);
}



interface IProtocol{

    public function getApp();
}