<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-22
 * Time: 下午11:26
 */

class Type{
    const SYSTEM = 0;//系统消息
    const LOGIN = 1;//登录
    const CHAT = 2;//聊天
    const GROUP_CHAT = 3;//群聊
}

class Error{
    const NO_ERROR = 0;//没有错误
    const LOGIN_ERROR = 1;//用户名或密码错误
    const NOT_LOGIN = 2;//请先登录
    const TYPE_ERROR = 3;//消息类型错误
    const MESSAGE_EMPTY = 4;//消息内容空
    const USER_NOT_EXISTS = 5;//用户不存在
    const USER_NOT_ONLINE = 6;//用户不在线
    const GROUP_CHAT_TO_ERROR = 7;//群聊接收者必须是数组
}