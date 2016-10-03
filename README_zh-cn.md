Jegarn
======

基于Swoole/Redis/Msgpack的高性能聊天框架,集成认证/单聊/群聊/聊天室/离线存储等功能.

官网: [https://jegarn.com](https://jegarn.com)

[English Introduction](README.md)

Demo
------

###### WebApp Demo: [https://jegarn.com/minions.html](https://jegarn.com/minions.html)

该网页演示系统参照WebQQ,目前支持:

1. 注册/登录,多账号同时在线
2. 添加好友,好友申请审核,好友之间单聊
3. 创建群组,申请入群,入群申请审核,组成员群聊
4. 创建聊天室,聊天室成员群聊
5. 推荐会员列表,推荐群组列表,推荐聊天室列表
6. 自动回复的机器人


###### Android Demo [https://jegarn.com/minions.apk](https://jegarn.com/minions.apk)

安卓应用支持:

1. 登录聊天系统
2. 好友列表,好友之间单聊
3. 群组列表,组成员群聊
4. 聊天室列表,聊天室成员群聊
5. 持久化存储单聊/群聊/聊天室消息
6. 消息通知,新消息手机振动

###### iOS Demo [iOS聊天项目下载](https://github.com/Yaoguais/ios-on-the-way/tree/master/minions)

苹果应用支持:

1. 登录聊天系统
2. 好友列表,好友之间单聊
3. 群组列表,组成员群聊
4. 聊天室列表,聊天室成员群聊

注: 下载项目即可在Xcode中运行

###### 演示

<a href="https://jegarn.com/images/jegarn_demo.gif" target="_blank"><img src="https://jegarn.com/images/jegarn_demo.gif" width="400"></a>


Requirements
------

* PHP 5.3.10 or later
* Swoole 1.7.20 or newer
* Msgpack 0.5.7
* Redis 2.8.22

对于聊天系统"minions", 要求安装:

* yaf 2.3.5
* mysql 5.6.26
* nginx 1.8.0




Installation
------


	wget http://pecl.php.net/get/swoole-1.7.20.tgz
	tar -zvxf swoole-1.7.20.tgz 
	cd swoole-1.7.20
	phpize
	./configure --enable-openssl
	make && make install
	echo "extension=swoole.so" > /etc/php.d/swoole.ini
	
	pecl install msgpack
	yum -y install redis

更加详细的安装过程,请参考 [INSTALL.md](./INSTALL.md).





Tutorial
------

#### Start Server

启动单个聊天服务器

	$ php server.php 192.168.1.2 9501

再启动一个聊天服务器

	$ php server.php 192.168.1.2 9502

启动一个WebSocket聊天服务器
	
	$ php webserver.php 192.168.1.2 9503

所有的聊天服务器如果共用同一个Redis服务,那么它们就组成了一个集群,两两之间可以互相转发消息.


#### Start Client

	$ php robot_counter.php


#### Configuration

	// 用来保存消息的数据库配置
	'cache'       => [
        'host'     => '192.168.1.2',
        'port'     => 6379,
        'timeout'  => 0.0,
        'password' => null
    ],
	// 服务器配置,完全等同Swoole的配置项
    'server'      => [
        'host'          => '192.168.199.243',
        'port'          => 9501,
        'worker_num'    => 4,
        'dispatch_mode' => 2,
        'log_file'      => __DIR__ . '/../logs/server_swoole.log',
        'daemonize'     => 1,
        'ssl_cert_file' => __DIR__ . '/ssl.crt',
        'ssl_key_file' => __DIR__ . '/ssl.key'
    ],
	// 监听器列表,目前有身份验证/通知/单聊/群聊/聊天室消息.
    'listener' => [
        'jegarn\listener\AuthPacketListener', // 身份认证总是添加到第一个
        'jegarn\listener\NotificationPacketListener',
        'jegarn\listener\ChatPacketListener',
        'jegarn\listener\GroupChatPacketListener',
        'jegarn\listener\ChatroomPacketListener'
    ]





Contribution
------

如果你想为Jegarn聊天框架作出一些贡献,请参考以下几种方式:

* 贡献你的代码,提交Pull Request
* 提交你在使用过程中发现的[Bug](https://github.com/Yaoguais/jegarn/issues)
* 帮助我们完善Wiki





License
------

Apache License Version 2.0
