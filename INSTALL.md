# Welcome To Jegarn Install Guide

This article recorded that I set up the website "jegarn.com", and there are six steps.



## Install PHP

install php5.6 from webtatic repo mirror. before install php, you should install libxml2.

	# yum -y install libxml2 libxml2-devel
	# yum -y install openssl openssl-devel
	# rpm -Uvh https://mirror.webtatic.com/yum/el6/latest.rpm
	# yum -y install php56w php56w-common php56w-cli php56w-devel php56w-fpm php56w-gd php56w-mbstring php56w-mcrypt php56w-pdo php56w-mysql php56w-xml php56w-opcache php56w-pear
	# cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime
	# yum install -y ntpdate
	# ntpdate us.pool.ntp.org
	# vim /etc/php.ini
	/*
	date.timezone = PRC
	*/



	
## Install Swoole & Yaf & Redis & Msgpack
	
try again when install failed for network, php channel and so on.

	# wget http://pecl.php.net/get/swoole-1.7.20.tgz
	# tar -zvxf swoole-1.7.20.tgz 
	# cd swoole-1.7.20
	# phpize
	# ./configure --enable-openssl
	# make && make install
	# vim /etc/php.d/swoole.ini
	/*
	extension=swoole.so
	*/
	# pecl install yaf
	# vim /etc/php.d/yaf.ini
	/*
	extension=yaf.so
	yaf.use_namespace = 1
	*/
	# pecl install redis
	# vim /etc/php.d/redis.ini
	/*
	extension=redis.so
	*/
	# wget http://pecl.php.net/get/msgpack-0.5.7.tgz
	# tar -zvxf msgpack-0.5.7.tgz
	# cd msgpack-0.5.7
	# phpize
	# ./configure
	# make && make install
	# vim /etc/php.d/msgpack.ini
	/*
	extension=msgpack.so
	*/


	

## Install Mysql

install mysql 5.7 by yum repo.

	# yum install http://dev.mysql.com/get/mysql57-community-release-el5-7.noarch.rpm
	# yum install mysql-community-server mysql-community-devel mysql-community-common mysql-community-client mysql-community-embedded mysql-community-libs -y
	# vim /etc/my.cnf
	/*
	# ibdata1 is too bigger
	innodb_file_per_table=1
	*/
	# mysqld --user mysql --initialize
	# service mysqld start
	# grep 'temporary password' /var/log/mysqld.log
	# mysql -uroot -p
	> ALTER USER 'root'@'localhost' IDENTIFIED BY 'xxx';
	> flush privileges;
	> exit
	
	
	

## Install Redis

there is no redis yum repo, install it by source code.

	# wget https://github.com/antirez/redis/archive/2.8.22.tar.gz
	# tar -zvxf 2.8.22.tar.gz 
	# cd redis-2.8.22
	# make
	# cd utils
	# ./install_server.sh
	notice: go on, and executable path: /root/redis-2.8.22/src/redis-server
	# mv /etc/init.d/redis_6379 /etc/init.d/redis
	# vim /etc/redis/6379.conf
	/*
	# bind 192.168.1.100 10.0.0.1
	bind 127.0.0.1
	*/
	# service redis restart



## Install Git Nginx and pull project

	# yum install http://pkgs.repoforge.org/rpmforge-release/rpmforge-release-0.5.3-1.el6.rf.x86_64.rpm
	# yum --disablerepo=base,updates --enablerepo=rpmforge-extras install -y git
	# yum -y install nginx18
	# cd /etc/nginx
	# mkdir ssl
	# cd ssl
	# openssl req -x509 -nodes -days 36500 -newkey rsa:2048 -keyout server.key -out server.crt
	# cd ../
	# chown -R nginx:nginx ssl
	# yum -y install vim lrzsz
	# cd /var/www
	# rm -rf *
	# git clone git@github.com:Yaoguais/jegarn.git
	# vim /etc/nginx/conf.d/80.conf
	/*
	server {
        listen          80;
        server_name     jegarn.com;
        charset         utf-8;

        set             $bootstrap          "index.php";
        set             $host_path          "/var/www/jegarn/examples/web-chat-system/app/public";
        root            $host_path;

        location / {
                index index.html $bootstrap;
                try_files $uri $uri/ /$bootstrap?$args;
        }

        location ~ \.php$ {
                fastcgi_split_path_info  ^(.+\.php)(.*)$;
                set $fsn /$bootstrap;
                if (-f $document_root$fastcgi_script_name){
                        set $fsn $fastcgi_script_name;
                }
                fastcgi_pass   127.0.0.1:9000;
                include        fastcgi_params;
                fastcgi_param  PATH_TRANSLATED  $document_root$fsn;
                fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        }

        # prevent nginx from serving dotfiles (.htaccess, .svn, .git, etc.)
        location ~ /\. {
                deny all;
                access_log off;
                log_not_found off;
        }
	}
	*/
	then add port 443.
	create mysql database and user
	# mysql -u root -p
	> create database minions default charset utf8;
	> grant all privileges on minions.* to minions@localhost identified by 'xxx';
	> flush privileges;
	> exit

init a defualt user data with run script install.php

	# cd /var/www/jegarn/examples/web-chat-system
	# php install.php

change owner of php-fpm
	
	# vim /etc/php-fpm.d/www.conf
	/*
	user = nginx
	group = nginx
	*/

change owner of upload

	# cd /var/www/jegarn/examples/web-chat-system/app/public
	# chown nginx:nginx upload -R

create a user to run server webserver robot_counter

	# useradd -m -s "/bin/bash" yaoguai
	# passwd yaoguai
	# cd /var/www/jegarn/examples/web-chat-system
	# mkdir logs
	# chown yaoguai:yaoguai logs
	# su yaoguai
	$ php server.php 123.56.79.160 9501
	$ php webserver.php 123.56.79.160 9503
	$ cd logs
	$ nohup php ../robot_counter.php &

## Open Browser and Test

finally, the chat system "minions" was set up.

	
	
	
	


