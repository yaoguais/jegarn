<?php

/*

使用redis作为数据库
数据库15: 系统表 key:uid(auto_increment) key:gid(auto_increment)
数据库0：用户表，key:uid value:map(username,nickname,password,create_time)
数据库1：组表，key:gid value:map(name,description,create_time)
数据库2：用户连接表：key:uid value:conn_id(ip_port_fd)
数据库3: 组成员表 : key:gid value:set of uid
数据库4: 用户关系表: key:uid+'_'+target_uid(uid<target_uid) value:relation(1:friend 2:black)
数据库5: 离线消息表: key:uid value:list of message
数据库6：消息记录表：key:module_type_id value:list of message(存到mysql数据库中要快点 list的分页太慢)


协议：
2字节bodyLength + 1字节打包方式 + 1字节应用ID + 1字节应用模型 + 4字节UID/GID + bodyString
当前body使用MsgPack进行打包


打包方式当前支持：
'0' : 原生字符串
'1' : php
'2' : MsgPack(默认)

应用ID
'1' : 聊天
'2' : 群聊

模型当前为保留字段：必须为0(应用场景：直接通过模型决定消息是否进行转发，减少流量)

uid/gid 使用pack('N',$id)进行打包,unsigned long(0-4,294,967,295) 支持42亿用户或群组数
body    使用pack('n',$body)进行打包，unsigned short(0-65535) 支持6万字节

消息的拼接使用SwooleBuffer
 */

namespace minions;

interface ICache {

    const ERROR = -1;
    const SUCCESS = 1;

    /**
     * 自增并获取自增后的值
     * @param $key
     * @param int $step
     * @return int 增加后的值
     */
    public function increase($key,$step=1);

    /**
     * 自减并获取自减后的值
     * @param $key
     * @param int $step
     * @return int 减少后的值
     */
    public function decrease($key,$step=1);

    /**
     * 设置Map
     * @param $key
     * @param array $map
     * @return int status
     */
    public function setMap($key,$map);

    /**
     * @param $key
     * @param $map //成功后自动填充
     * @return int status
     */
    public function getMap($key,&$map);

    /**
     * @param $key
     * @param $value
     * @return int status
     */
    public function addToSet($key,$value);

    /**
     * @param $key
     * @param $value //成功后自动填充原先的值
     * @return int status
     */
    public function removeFromSet($key,&$value);

    /**
     * @param $key
     * @param $value
     * @return int status
     */
    public function pushToList($key,$value);

    /**
     * @param $key
     * @param $value //成功后自动填充
     * @return int status
     */
    public function popFromList($key,&$value);

    /**
     * @param $key
     * @param $start
     * @param $end -1 for all
     * @return mixed failed:null success:array
     */
    public function getList($key,$start,$end=-1);
}