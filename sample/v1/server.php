<?php
/**
 * Created by PhpStorm.
 * User: yaoguai
 * Date: 15-6-22
 * Time: 下午7:57
 */

/**
 * 简单的json协议
 * 聊天：
 * {
    type : "chat",
    from : 1,
    to   : 2,
    message : "hello"
   }
 * 群聊：
 * {
    type : "group_chat",
    from : 1,
    to  :[2,3,4],
    message : "hello"
 * }
 * 系统回复：
 * {
    type : "system",
    error : 1
 * }
 * Class Server
 */

define('DEBUG',true);

error_reporting(E_ERROR);

require('class.php');

class Server{

    public static $app;

    private static $_fdMap;

    private static $_uidFdMap;

    private static $_messageCache;

    private static $_userTable = [
        [
            'uid' => 1,
            'username' => 'Tom',
            'password' => '123456',
            'token' => 'Tom111'
        ],
        [
            'uid' => 2,
            'username' => 'Tina',
            'password' => '123456',
            'token' => 'Tina222'
        ],
        [
            'uid' => 3,
            'username' => 'Sandy',
            'password' => '123456',
            'token' => 'Sandy333'
        ]
    ];

    public static function addClient($fd,$user){
        self::$_fdMap[$fd] = $user;
    }

    public static function removeClient($fd){
        unset(self::$_fdMap[$fd]);
    }

    public static function send($fd,$message){
        if(DEBUG){
            echo "send:\n";
            print_r($message);
        }
        return self::$app->send($fd,json_encode($message)."\r\n");
    }

    /**
     * 登录接口：
     * 成功返回[
     * type : "system",
     * error : 0,
     * uid : $uid,
     * token : $token
     * ]
     * @param $username
     * @param $password
     * @return null/$user
     */
    public static function login($username,$password){
        foreach(self::$_userTable as $user){
            if($user['username']==$username && $user['password']==$password){
                return $user;
            }
        }
        return null;
    }

    /**
     * 根据UID获取用户
     * @param $uid
     * @return null/$user
     */
    public static function getUserByUid($uid){
        foreach(self::$_userTable as $user){
            if($user['uid'] == $uid){
                return $user;
            }
        }
        return null;
    }
    /**
     * 根据ID跟TOKEN获取用户
     * @param $uid
     * @param $token
     * @return null/$user
     */
    public static function getUserByToken($uid,$token){
        foreach(self::$_userTable as $user){
            if($user['uid']==$uid && $user['token']==$token){
                return $user;
            }
        }
        return null;
    }

    /**
     * @param $fd
     * @param $user
     * @return bool
     */
    public static function addUser($fd,$user){
        self::addClient($fd,$user);
        self::$_uidFdMap[$user['uid']] = $fd;
        return true;
    }

    /**
     * @param $fd
     * @param $user
     * @return bool
     */
    public static function removeUser($fd,$user=null){
        if(null === $user){
            $user = self::$_fdMap[$fd];
        }
        self::removeClient($fd);
        unset(self::$_uidFdMap[$user['uid']]);
        return true;
    }

    /**
     * 给用户发送消息
     * @param $uid
     * @param $message
     * @return bool
     */
    public static function sendUserMessage($uid,$message){
        $toUserFd = self::$_uidFdMap[$uid];
        //写到缓存的消息中去
        if(empty($toUserFd)){
            self::$_messageCache[$uid][] = $message;
            return false;
        }else{
            Server::send($toUserFd,$message);
            return true;
        }
    }

    /**
     * 发送离线消息
     * @param $uid
     */
    public static function sendOfflineMessage($uid){
        $fd = self::$_uidFdMap[$uid];
        foreach(self::$_messageCache[$uid] as &$message){
            if(self::send($fd,$message)){
                unset($message);
            }
        }
    }

    public static function info($tag = ''){
        if(DEBUG){
            if($tag){
                echo $tag,"\r\n";
            }
            print_r(self::$_fdMap);
            print_r(self::$_uidFdMap);
        }
    }
}

Server::$app = new swoole_server("0.0.0.0", 9501);

Server::$app->set([
    'worker_num' => 4,
    'open_eof_check' => true,
    'package_eof' => "\r\n"
]);

Server::$app->on('connect', function ($server, $fd, $from_id){

});

Server::$app->on('close', function ($server, $fd, $from_id) {
    Server::removeUser($fd);
});

Server::$app->on('receive', function ($serv, $fd, $from_id, $data) {
    $data = (array)json_decode($data);
    if(DEBUG){
        echo "recv:\n";
        print_r($data);
    }
    if(Type::LOGIN === $data['type']){
        $user = Server::login($data['username'],$data['password']);
        if(empty($user)){
            Server::send($fd,[
                'type' => Type::LOGIN,
                'error' => Error::LOGIN_ERROR
            ]);
        }else{
            Server::addUser($fd,$user);
            Server::send($fd,[
                'type' => Type::LOGIN,
                'error' => Error::NO_ERROR,
                'uid' => $user['uid'],
                'token' => $user['token']
            ]);
            //如果有离线消息,那么依次发送里面消息
            Server::sendOfflineMessage($user['uid']);
            Server::info();
        }
    }else if(Type::CHAT === $data['type']){
        if(empty($data['message'])){
            Server::send($fd,[
                'type' => Type::CHAT,
                'error' => Error::MESSAGE_EMPTY
            ]);
            return;
        }
        if( null == ($receiveUser = Server::getUserByUid($data['to']))){
            Server::send($fd,[
                'type' => Type::CHAT,
                'error' => Error::USER_NOT_EXISTS
            ]);
            return;
        }
        $user = Server::getUserByToken($data['uid'],$data['token']);
        if(empty($user)){
            Server::send($fd,[
                'type' => Type::CHAT,
                'error' => Error::LOGIN_ERROR
            ]);
            return;
        }
        Server::info($user['username']);
        if(Server::sendUserMessage($data['to'],[
            'type' => Type::CHAT,
            'from' => $user['uid'],
            'fromUsername' => $user['username'],
            'message' => $data['message']
        ])){
            Server::send($fd,[
                'type' => Type::CHAT,
                'error' => Error::NO_ERROR
            ]);
        }else{
            Server::send($fd,[
                'type' => Type::CHAT,
                'error' => Error::USER_NOT_ONLINE
            ]);
        }
    }else if(TYPE::GROUP_CHAT === $data['type']){
        if(empty($data['message'])){
            Server::send($fd,[
                'type' => Type::GROUP_CHAT,
                'error' => Error::MESSAGE_EMPTY
            ]);
            return;
        }
        if(!is_array($data['to'])){
            Server::send($fd,[
                'type' => Type::GROUP_CHAT,
                'error' => Error::GROUP_CHAT_TO_ERROR
            ]);
            return;
        }
        $user = Server::getUserByToken($data['uid'],$data['token']);
        if(empty($user)){
            Server::send($fd,[
                'type' => Type::GROUP_CHAT,
                'error' => Error::LOGIN_ERROR
            ]);
            return;
        }
        foreach($data['to'] as $uid){
            Server::sendUserMessage($uid,[
                'type' => Type::GROUP_CHAT,
                'from' => $user['uid'],
                'fromUsername' => $user['username'],
                'message' => $data['message']
            ]);
        }
        Server::send($fd,[
            'type' => Type::GROUP_CHAT,
            'error' => Error::NO_ERROR
        ]);
    }else{
        Server::send($fd,[
            'type' => Type::SYSTEM,
            'error' => Error::TYPE_ERROR
        ]);
    }
});

Server::$app->start();
/**
连接到来：
这个时候还不知道是哪个用户，但是知道其IP地址，可以根据IP地址做限制。
应用场景：IpFilter
 *
消息到来：第一次
生成唯一的connectKey,connectKey映射user
connectKey = appGUid+fd
appGUid = ip+port
connectKey = ip+port+fd
可以使用redis等key-value内存数据库实现

发送消息：
 接收方如果是离线用户：
    缓存到消息队列（每个gUid一个队列）
 接收方如果是在线用户：
    如果是本服务器的用户：(1)
          如果是本进程管理的用户：直接转发
          不是本进程管理的用户：通过管道转发到指定进程，该进程再处理
    如果不是本服务器的用户：
          通过socket转发到该服务器,然后进行(1)
          转发可以直接进行转发，权限判断时通过IP地址进行过滤即可



 添加用户：
 1.本进程生成fdGUidMap
 2.内存数据库生成connectKeyUserMap
 查找用户：
 1.本进程是否记录该用户
 2.内存数据库是否记录该用户
 删除用户：
 1.本进行删除该用户
 2.内存数据库删除该用户
 *
 用户数据：gUid
 1个用户4b数据pack成二进制，1百万用户4MB数据
 用户昵称、备注等信息通过restful

 消息类别：一对一
         一对多
*/