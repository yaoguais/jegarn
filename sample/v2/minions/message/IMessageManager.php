<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-23
 * Time: 下午12:39
 */

namespace minions\message;
use minions\user\UserBase;

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