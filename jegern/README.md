使用redis作为数据库
数据库15: 系统表 key:uid(auto_increment) key:gid(auto_increment) key:server(list of host:ip)
数据库0：用户表，key:uid value:map(username,nickname,password,create_time)
数据库1：组表，key:gid value:map(name,description,create_time)
数据库2：用户连接表：key:uid value:conn_id(ip_port_pid_tid_fd)
数据库3: 组成员表 : key:gid value:set of uid
数据库4: 用户关系表: key:uid+'_'+target_uid(uid<target_uid) value:relation(1:friend 2:black)
数据库5: 离线消息表: key:uid value:list of message//string glue
数据库6：消息记录表：key:module_type_id value:list of message(存到mysql数据库中要快点 list的分页太慢)


协议：
2字节bodyLength + 1字节打包方式 + 1字节应用ID + 1字节应用模型 + 4字节UID/GID + bodyString
当前body使用MsgPack进行打包


打包方式当前支持：
'0' : 原生字符串
'1' : php
'2' : MsgPack(默认)
'3' : Glue(粘贴,用\n)

应用ID
'0' : 系统
'1' : 验证
'2' : 聊天
'3' : 群聊



模型当前为保留字段：必须为0(应用场景：直接通过模型决定消息是否进行转发，减少流量)

uid/gid 使用pack('N',$id)进行打包,unsigned long(0-4,294,967,295) 支持42亿用户或群组数
body    使用pack('n',$body)进行打包，unsigned short(0-65535) 支持6万字节

消息的拼接使用SwooleBuffer

## 整个流程：##

##### 服务器启动：#####
添加当前服务器配置到"系统表",获取当前服务器列表，并与其他所有服务器建立连接。
建立连接的细节：每个task对一个服务器保持一个socket，只做发送（因为发送是主动的，接收是被动的）。


##### 验证流程：#####
如果是验证消息:
客户端发送账号密码过来，进行查库验证，验证通过后，生成conn_id插入到数据库中
当以后的请求到来，首先查询该用户是否在线。如果不在线，那么发送失败原因。

##### 聊天流程：#####
 接收方如果是离线用户：
    缓存到消息队列
 接收方如果是在线用户：
    如果是本服务器的用户：(1)
          如果是本进程管理的用户：直接转发
          不是本进程管理的用户：通过管道转发到指定进程，该进程再处理
    如果不是本服务器的用户：
          通过socket转发到目标服务器,然后进行(1)
          转发可以直接进行转发，权限判断时通过IP地址进行过滤即可

##### 群聊流程：#####
首先读取群聊的用户列表，排除当前用户，进行消息转发。

## 各种细节 ##

