<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-22
 * Time: 下午9:34
 *
 * 当worker_num = 1时可以使用
 * 当worker_num > 1时，由于worker在多进程当中,
 * 全局变量在多进程中也是多个副本，所以可以使用redis等解决
 */

define('DEBUG',false);

error_reporting(E_ERROR);

if($argc != 3){
    die("php client.php username password\n");
}

require('class.php');

class Client{
    public static $app;

    public static $user;

    public static function send($message){
        if(empty($message)){
            echo "message empty\n";
        }
        $message['uid'] = self::$user['uid'];
        $message['token'] = self::$user['token'];
        if(DEBUG){
            echo "send:\n";
            print_r($message);
        }
        self::$app->send(json_encode($message)."\r\n");
    }
}


Client::$app = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

Client::$app->set([
    'open_eof_check' => true,
    'package_eof' => "\r\n"
]);

Client::$app->on("connect", function(swoole_client $cli) {
    //进行登录操作
    Client::send([
        'type' => Type::LOGIN,
        'username' => $_SERVER['argv'][1],
        'password' => $_SERVER['argv'][2]
    ]);
});
Client::$app->on("receive", function(swoole_client $cli, $data){
    if(DEBUG){
        var_dump($data);
    }
    $data = (array)json_decode($data);
    if(DEBUG){
        echo "recv:\n";
        print_r($data);
    }
    if(!isset($data['type'])){
        echo "empty type\n";
        return;
    }
    if(isset($data['error']) && Error::NO_ERROR !== $data['error']){
        echo "error: {$data['error']} type: {$data['type']}\n";
        return;
    }
    if(Type::LOGIN == $data['type']){
        $user['uid'] = $data['uid'];
        $user['token'] = $data['token'];
        Client::$user = $user;
        echo "login success\n";
        //进行随机发送消息
        for($i=0;$i<1;++$i){
            $uid = $user['uid'] == 1 ? 3 : 1;
            $message = [
                'type' => Type::CHAT,
                'to' => $uid,
                'message' => 'hello'.rand(100000,999999)
            ];
            Client::send($message);
        }
        for($i=0;$i<1;++$i){
            $message = [
                'type' => Type::GROUP_CHAT,
                'to' => [2,3],
                'message' => 'hello'.rand(10000000,99999999)
            ];
            Client::send($message);
        }
        return;
    }else if(Type::CHAT == $data['type']){
        if(!isset($data['error'])){
            echo "chat:\r\n{$data['fromUsername']}: {$data['message']}\n";
        }
    }else if(Type::GROUP_CHAT == $data['type']){
        if(!isset($data['error'])){
            echo "groupChat:\r\n{$data['fromUsername']}: {$data['message']}\n";
        }
    }
});
Client::$app->on("error", function(swoole_client $cli){
    echo "error\n";
});
Client::$app->on("close", function(swoole_client $cli){
    echo "Connection close\n";
});


/**
 * 作用：检测worker_num > 1 多进程应该注意的细节：
 * 多进程只能通过IPC或者redis等手段实现数据集中式管理。
 * 所以当前的Server实现是不符合多进程模型的
 *
swoole_timer_add(3000, function($interval) {
    if(Client::$user){
        $user = Client::$user;
        $uid = $user['uid'] == 1 ? 3 : 1;
        $message = [
            'type' => Type::CHAT,
            'to' => $uid,
            'message' => 'hello'.rand(100000,999999)
        ];
        Client::send($message);
    }
});
 */

Client::$app->connect('127.0.0.1', 9501);