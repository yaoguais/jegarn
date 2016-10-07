Jegarn
======

A high performance chat system, based on swoole, redis and msgpack.
Integrated authorization, chat, groupchat, chatroom and offline storage.

And client-sdk for iOS, Android, JS, and PHP are provided.

Website: [https://jegarn.com](https://jegarn.com)




@Deprecated
======

This project has been moved to [https://github.com/jegarn](https://github.com/jegarn).




Support SDKs and Demos
------

- [iOS SDK](https://github.com/Yaoguais/jegarn/blob/master/sdk/ios/src/README.md)
- [Android SDK](https://github.com/Yaoguais/jegarn/blob/master/sdk/android/src/README.md)
- [JS SDK](https://github.com/Yaoguais/jegarn/blob/master/sdk/js/src/README.md)
- [PHP SDK](https://github.com/Yaoguais/jegarn/blob/master/sdk/php/src/README.md)

We also build some applications for showing that how to use those SDKs.

<a href="https://jegarn.com/images/jegarn_demo.gif"><img src="https://jegarn.com/images/jegarn_demo_thumb.gif" width="200"></a>

[Visit the WebApp Demo](https://jegarn.com/minions.html),
and download the [Source Code](https://github.com/Yaoguais/jegarn/blob/master/examples/web-chat-system).

[Download Android APP](https://jegarn.com/minions.apk),
and download the [Source Code](https://github.com/Yaoguais/android-on-the-way/tree/master/android-chat-system).

[Download the iOS Project](https://github.com/Yaoguais/ios-on-the-way/tree/master/minions),
and run it by Simulator or your iPhone.

Demos Functions:

1. register and multi accounts login
2. add friends and chat with friends
3. create chat group and chat with every member
4. create chat room and chat with every online people
5. a always online robot "Counter" for sending a number to everybody




Requirements
------

* PHP 5.3.10 or later
* Swoole 1.7.20 or newer
* Msgpack 0.5.7
* Redis 2.8.22

And for demo chat system "minions", it requires:

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

for more details, see [INSTALL.md](./INSTALL.md).





Tutorial
------

#### Start Server

start a tcp server

	$ php server.php 192.168.1.2 9501

start another tcp server

	$ php server.php 192.168.1.2 9502

start a websocket server
	
	$ php webserver.php 192.168.1.2 9503

all server use same cache can communicate with each other, and they form a cluster.


#### Start Client

	$ php robot_counter.php


#### Configuration

	// cache server to storage users and messages
	'cache'       => [
        'host'     => '192.168.1.2',
        'port'     => 6379,
        'timeout'  => 0.0,
        'password' => null
    ],
	// server config, same as configuration of swoole
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
	// listeners, you can choose your own packet listeners
    'listener' => [
        'jegarn\listener\AuthPacketListener', // always add to first, not authorized user would do nothing
        'jegarn\listener\NotificationPacketListener',
        'jegarn\listener\ChatPacketListener',
        'jegarn\listener\GroupChatPacketListener',
        'jegarn\listener\ChatroomPacketListener'
    ]





Contribution
------

Thank you very much because of your contribution, and I believe we can make jegarn better.
Some ways available:

* contribute your code via Pull Request
* write down your [Issues](https://github.com/Yaoguais/jegarn/issues)
* make the Wiki complete





License
------

Apache License Version 2.0
