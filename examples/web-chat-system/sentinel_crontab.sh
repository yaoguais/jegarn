#!/bin/bash
# the sentinel of nginx, php-fpm, mysql, redis, jegarn-server

if [ "$(netstat -ano | grep LISTEN | grep 443)" == "" ]; then
    service nginx restart
fi

if [ "$(netstat -ano | grep LISTEN | grep 9000)" == "" ]; then
    service php-fpm restart
fi

if [ "$(netstat -ano | grep LISTEN | grep 3306)" == "" ]; then
    service mysqld restart
fi

if [ "$(netstat -ano | grep LISTEN | grep 6379)" == "" ]; then
    service redis restart
fi

if [ "$(netstat -ano | grep LISTEN | grep 9501)" == "" ]; then
    cd /var/www/jegarn/examples/web-chat-system
    su yaoguai -c "php server.php 123.56.79.160 9501"
fi

if [ "$(netstat -ano | grep LISTEN | grep 9503)" == "" ]; then
    cd /var/www/jegarn/examples/web-chat-system
    su yaoguai -c "php webserver.php 123.56.79.160 9503"
fi

if [ "$(ps -ef | grep robot_counter | wc -l)" != "2" ]; then
    cd /var/www/jegarn/examples/web-chat-system/logs
    su yaoguai -c "nohup php ../robot_counter.php &"
fi

