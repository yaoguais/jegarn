<?php
/**
 * parts:
 * 1. packet
 * 2. util
 * 3. cache
 * 4. manager
 * 5. server
 * 6. client
 */

/********************* PACKET ***************************/
namespace jegarn\packet{
    // client packet is not same sa server, so client can't insert packet into cache database directly
    class Base{
        const TYPE = null;
        public $from;
        public $to;
        public $type;
        public $content;
        public function __construct(){
            $this->type = static::TYPE;
        }
        public function isFromSystemUser(){
            return 'system' === $this->from;
        }
        public function setFromSystemUser(){
            $this->from = 'system';
        }
        public function setToSystemUser(){
            $this->to = 'system';
        }
        public function convertToArray(){
            return ['from' => $this->from, 'to' => $this->to, 'type' => $this->type, 'content' => $this->content];
        }
        public static function getPacketFromArray(array $data){
            $packet = new static;
            $packet->from = $data['from'];
            $packet->to = $data['to'];
            $packet->type = $data['type'];
            $packet->content = $data['content'];
            return $packet;
        }
        public function getPacketFromPacket(Base $packet){
            if($packet->type == $this->type){
                $this->from = $packet->from;
                $this->to = $packet->to;
                $this->type = $packet->type;
                $this->content = $packet->content;
                return $this;
            }
            return null;
        }
    }
    class Auth extends Base{
        const TYPE = 'auth';
        const STATUS_NEED_AUTH = 1;
        const STATUS_AUTH_SUCCESS = 2;
        const STATUS_AUTH_FAILED  = 3;
        public function __construct(){
            parent::__construct();
            $this->from = 0;
            $this->setToSystemUser();
        }
        public function getUid(){
            return isset($this->content['uid']) ? $this->content['uid'] : null;
        }
        public function setUid($value){
            $this->content['uid'] = intval($value);
        }
        public function getAccount(){
            return isset($this->content['account']) ? $this->content['account'] : null;
        }
        public function setAccount($value){
            $this->content['account'] = $value;
        }
        public function getPassword(){
            return isset($this->content['password']) ? $this->content['password'] : null;
        }
        public function setPassword($value){
            $this->content['password'] = $value;
        }
        public function getStatus(){
            return isset($this->content['status']) ? $this->content['status'] : null;
        }
        public function setStatus($value){
            $this->content['status'] = $value;
        }
    }
    abstract class HasSubType extends Base{
        const TYPE = 'chat';
        const SUB_TYPE = null;
        public function __construct(){
            parent::__construct();
            $this->content['type'] = static::SUB_TYPE;
        }
    }
    class Chat extends HasSubType{}
    abstract class GroupBase extends HasSubType{
        public function __construct(){
            parent::__construct();
        }
        public function isSendToAll(){
            return 'all' == $this->to;
        }
        public function setSendToAll(){
            $this->to = 'all';
        }
        public function getGroupId(){
            return isset($this->content['group_id']) ? $this->content['group_id'] : null;
        }
        public function setGroupId($groupId){
            $this->content['group_id'] = $groupId;
        }
    }
    class GroupChat extends GroupBase{
        const TYPE = 'groupchat';
    }
    class Chatroom extends GroupBase {
        const TYPE = 'chatroom';
    }
    class TextChat extends Chat{
        const SUB_TYPE = 'text';
        public function __construct(){
            parent::__construct();
        }
        public function getText(){
            return isset($this->content['text']) ? $this->content['text'] : null;
        }
        public function setText($value){
            $this->content['text'] = $value;
        }
    }
    class TextGroupChat extends GroupChat{
        const SUB_TYPE = 'text';
        public function __construct(){
            parent::__construct();
        }
        public function getText(){
            return isset($this->content['text']) ? $this->content['text'] : null;
        }
        public function setText($value){
            $this->content['text'] = $value;
        }
    }
    class TextChatroom extends Chatroom{
        const SUB_TYPE = 'text';
        public function __construct(){
            parent::__construct();
        }
        public function getText(){
            return isset($this->content['text']) ? $this->content['text'] : null;
        }
        public function setText($value){
            $this->content['text'] = $value;
        }
    }
    class Notification extends HasSubType {
        const TYPE = 'notification';
        public function __construct(){
            parent::__construct();
            $this->setFromSystemUser();
        }
    }
    class UserNotification extends Notification{
        public function setUserId($uid){
            $this->content['uid'] = $uid;
        }
        public function getUserId(){
            return isset($this->content['uid']) ? $this->content['uid'] : null;
        }
    }
    class FriendNotification extends UserNotification{
        public function __construct(){
            parent::__construct();
        }
    }
    class FriendRequestNotification extends FriendNotification{
        const SUB_TYPE = 'friend_request';
        public function __construct(){
            parent::__construct();
        }
    }
    class FriendAgreeNotification extends FriendNotification{
        const SUB_TYPE = 'friend_agree';
        public function __construct(){
            parent::__construct();
        }
    }
    class FriendRefusedNotification extends FriendNotification{
        const SUB_TYPE = 'friend_refused';
        public function __construct(){
            parent::__construct();
        }
    }
    class GroupNotification extends UserNotification{
        public function getGroupId(){
            return isset($this->content['group_id']) ? $this->content['group_id'] : null;
        }
        public function setGroupId($groupId){
            $this->content['group_id'] = $groupId;
        }
        public function getGroupName(){
            return isset($this->content['group_name']) ? $this->content['group_name'] : null;
        }
        public function setGroupName($name){
            $this->content['group_name'] = $name;
        }
    }
    class GroupRequestNotification extends GroupNotification{
        const SUB_TYPE = 'group_request';
        public function __construct(){
            parent::__construct();
        }
    }
    class GroupAgreeNotification extends GroupNotification{
        const SUB_TYPE = 'group_agree';
        public function __construct(){
            parent::__construct();
        }
    }
    class GroupRefusedNotification extends GroupNotification{
        const SUB_TYPE = 'group_refused';
        public function __construct(){
            parent::__construct();
        }
    }
    class GroupInvitedNotification extends GroupNotification{
        const SUB_TYPE = 'group_invited';
        public function __construct(){
            parent::__construct();
        }
    }
    class GroupDisbandNotification extends GroupNotification{
        const SUB_TYPE = 'group_disband';
        public function __construct(){
            parent::__construct();
        }
    }
    class GroupQuitNotification extends GroupNotification{
        const SUB_TYPE = 'group_quit';
        public function __construct(){
            parent::__construct();
        }
    }
}
/********************* UTIL ***************************/
namespace jegarn\util{
    abstract class ConvertUtil {
        public static function pack($mixed){
            return msgpack_pack($mixed);
        }
        public static function unpack($string){
            return msgpack_unpack($string);
        }
    }
    abstract class TextUtil {
        public static function isEmptyString($s){
            return $s === null || $s === '';
        }
        public static function generateGUID(){
            return md5(uniqid(mt_rand(), true));
        }
    }
}
/********************* CACHE ***************************/
namespace jegarn\cache{
    use Exception;
    use Redis;
    class Cache {
        private static $instance;
        private function __construct(){}
        private function __clone() {}
        protected $cache;
        protected $config;
        public static function getInstance(){
            if(null === self::$instance) self::$instance = new self();
            $instance = & self::$instance;
            if($instance->config !== null && !$instance->cache){
                ini_set('default_socket_timeout', -1);
                $c = $instance->config;
                $instance->cache= new Redis();
                if(!$instance->cache->connect($c['host'], $c['port'], $c['timeout'])){
                    throw new Exception('cache server connect failed');
                }
                if(isset($c['password']) && trim($c['password']) != ""){
                    if(!$instance->cache->auth($c['password'])){
                        throw new Exception('cache server auth failed');
                    }
                }
            }
            return $instance;
        }
        public function initConfig($config){
            $this->config = $config;
        }
        public function destroy(){
            if($this->cache){
                $this->cache->close();
                $this->cache = null;
            }
        }
        public function __destruct(){
            $this->destroy();
        }
        public function scan(&$iterator, $pattern = '', $count = 0){
            return $this->cache->scan($iterator, $pattern, $count);
        }
        public function __call($name, $arguments){
            return call_user_func_array([$this->cache, $name], $arguments);
        }
    }
}
/********************* MANAGER ***************************/
namespace jegarn\manager{
    use Exception;
    use Redis;
    use jegarn\cache\Cache;
    use jegarn\util\ConvertUtil;
    class BaseManager {
        private static $instance;
        private function __construct(){}
        private function __clone() {}
        /**
         * @param null $class
         * @return static
         * @throws \Exception
         */
        public static function getInstance($class = null){
            if($class === null){
                throw new Exception('class name can\'t be null');
            }else{
                if(!isset(self::$instance[$class])){
                    self::$instance[$class] = new $class;
                }
            }
            return self::$instance[$class];
        }
    }
    class UserManager extends BaseManager{
        public static function getInstance($class = __CLASS__){
            return parent::getInstance($class);
        }
        public function addUser($uid, $account, $password){
            return Cache::getInstance()->set($this->getCacheKey($account), ConvertUtil::pack(['uid' => $uid, 'account' => $account, 'password' => $this->encryptPassword($password)]));
        }
        public function encryptPassword($password){
            return hash('sha256', $password);
        }
        public function authPassword($input, $cryptPassword){
            return $input && $this->encryptPassword($input) == $cryptPassword;
        }
        public function getUser($account){
            if($str = Cache::getInstance()->get($this->getCacheKey($account))){
                return ConvertUtil::unpack($str);
            }
            return null;
        }
        public function removeUser($account){
            Cache::getInstance()->del($this->getCacheKey($account));
        }
        public function isUserOnline($uid){
            return Cache::getInstance()->exists('S_'.$uid);
        }
        public function getAllOnlineUsers(){
            $cache = Cache::getInstance();
            $cache->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);
            $it = null;
            $uidList = [];
            while($keys = $cache->scan($it, 'S_*', 1000)){
                foreach($keys as $key){
                    $uidList[] = substr($key,2);
                }
            }
            return $uidList;
        }
        protected function getCacheKey($id){
            return 'U_' . $id;
        }
    }
    class GroupManager extends BaseManager{
        public static function getInstance($class = __CLASS__){
            return parent::getInstance($class);
        }
        public function addGroup($gid){
            return true;
        }
        public function removeGroup($gid){
            return Cache::getInstance()->del($this->getCacheKey($gid)) > 0;
        }
        public function addGroupUser($gid, $uid){
            return Cache::getInstance()->sAdd($this->getCacheKey($gid), $uid);
        }
        public function isGroupUser($gid, $uid){
            return Cache::getInstance()->sIsMember($this->getCacheKey($gid), $uid);
        }
        public function addGroupUsers($gid, $uidList){
            array_unshift($uidList, $this->getCacheKey($gid));
            return call_user_func_array([Cache::getInstance(), 'sAdd'], $uidList);
        }
        public function getGroupUsers($gid){
            return Cache::getInstance()->sMembers($this->getCacheKey($gid));
        }
        public function removeGroupUser($gid, $uid){
            return Cache::getInstance()->sRem($this->getCacheKey($gid), $uid);
        }
        public function removeGroupUsers($gid, $uidList){
            array_unshift($uidList, $this->getCacheKey($gid));
            call_user_func_array([Cache::getInstance(), 'sRem'], $uidList);
        }
        protected function getCacheKey($id){
            return 'G_' . $id;
        }
    }
    class ChatroomManager extends BaseManager{
        public static function getInstance($class = __CLASS__){
            return parent::getInstance($class);
        }
        public function addChatroom($rid){
            return true;
        }
        public function removeChatroom($rid){
            return Cache::getInstance()->del($this->getCacheKey($rid)) > 0;
        }
        public function addChatroomUser($rid, $uid){
            return Cache::getInstance()->sAdd($this->getCacheKey($rid), $uid);
        }
        public function isGroupUser($rid, $uid){
            return Cache::getInstance()->sIsMember($this->getCacheKey($rid), $uid);
        }
        public function addChatroomUsers($rid, $uidList){
            array_unshift($uidList, $this->getCacheKey($rid));
            return call_user_func_array([Cache::getInstance(), 'sAdd'], $uidList);
        }
        public function getGroupUsers($rid){
            return Cache::getInstance()->sMembers($this->getCacheKey($rid));
        }
        public function removeChatroomUser($rid, $uid){
            return Cache::getInstance()->sRem($this->getCacheKey($rid), $uid);
        }
        public function removeChatroomUsers($rid, $uidList){
            array_unshift($uidList, $this->getCacheKey($rid));
            call_user_func_array([Cache::getInstance(), 'sRem'], $uidList);
        }
        protected function getCacheKey($id){
            return 'R_' . $id;
        }
    }
    class SocketManager extends BaseManager {
        protected $clients;
        public static function getInstance($class = __CLASS__){
            return parent::getInstance($class);
        }
        public function closeClient($host, $port){
            $id = $this->getClientId($host, $port);
            if(isset($this->clients[$id])){
                if(is_resource($this->clients[$id])){
                    fclose($this->clients[$id]);
                }
                unset($this->clients[$id]);
            }
        }
        public function sendClientMessage($host, $port, $message){
            $id = $this->getClientId($host, $port);
            if(empty($message)){
                return false;
            }
            if(!isset($this->clients[$id])){
                if($fd = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)){
                    $this->clients[$id] = $fd;
                }else{
                    return false;
                }
                if(!socket_connect($fd, $host, $port)){
                    return false;
                }
            }
            $fd = $this->clients[$id];
            $retryCount = 3;
            send_data:
            --$retryCount;
            $messageLen = strlen($message);
            $ret = socket_write($fd,$message, $messageLen);
            if($ret === $messageLen){
                return true;
            }else if($retryCount <= 0){
                return false;
            }else if(false === $ret){
                if(!socket_connect($fd, $host, $port)){
                    return false;
                }
                goto send_data;
            }else/* if($ret !== $messageLen)*/{
                $message = substr($message, $ret);
                goto send_data;
            }
        }
        protected function getClientId($host, $port){
            return $host . ':' . $port;
        }
    }
}
/********************* SERVER ***************************/
namespace jegarn\server{
    use jegarn\manager\SocketManager;
    use jegarn\packet\Base;
    use jegarn\util\ConvertUtil;
    use jegarn\cache\Cache;
    use Exception;
    class Server{
        protected $localAddress;
        protected $localPort;
        protected $remoteAddress;
        protected $remotePort;
        protected $serverId;
        protected $registerKey = 'L_server';
        protected $serverKey   = 'server_info';
        private static $_instance;
        private function __construct(){}
        private function __clone(){}
        public static function getInstance(){
            return self::$_instance ? self::$_instance : (self::$_instance = new static);
        }
        public function initConfig($config){
            if(isset($config['localAddress'], $config['localPort'], $config['remoteAddress'], $config['remotePort'], $config['serverId'])){
                $this->localAddress = $config['localAddress'];
                $this->localPort = $config['localPort'];
                $this->remoteAddress = $config['remoteAddress'];
                $this->remotePort = $config['remotePort'];
                $this->serverId = $config['serverId'];
                return $this;
            }else{
                throw new Exception('server config is not completed');
            }
        }
        public function register(){
            Cache::getInstance()->hSet($this->registerKey, $this->serverId, $this->localAddress . ':' . $this->localPort);
        }
        public function sendPacket(Base $packet){
            $data = $packet->convertToArray();
            $data[$this->serverKey] = $this->serverId;
            $packetStr = ConvertUtil::pack($data);
            SocketManager::getInstance()->sendClientMessage($this->remoteAddress, $this->remotePort, pack('N',strlen($packetStr)).$packetStr);
        }
    }
}
/********************* CLIENT ***************************/
namespace jegarn\client{
    use Exception;
    use jegarn\packet\Base;
    use jegarn\packet\Auth;
    use jegarn\util\ConvertUtil;
    use swoole_client;

    abstract class Client{
        protected $config;
        protected $uid;
        protected $account;
        protected $password;
        protected $host;
        protected $port;
        protected $socket;
        protected $running;
        protected $sessionKey = 'session_id';
        protected $sessionId;
        protected $authorized;
        protected $reconnectInterval;
        protected $packetListener;
        protected $sendListener;
        protected $errorListener;
        protected $connectListener;
        protected $disconnectListener;

        public function setConfig($config){
            $this->config = $config;
        }

        public function connect(){
            if(!$this->host || !$this->port){
                throw new Exception("host or port not config");
            }
            if(!$this->config){
                throw new Exception("client not config");
            }
            if(!$this->packetListener){
                throw new Exception("packetListener not config");
            }
            if(!$this->sendListener){
                throw new Exception("sendListener not config");
            }
            if(!$this->errorListener){
                throw new Exception("errorListener not config");
            }
            if(!$this->connectListener){
                throw new Exception("connectListener not config");
            }
            if(!$this->disconnectListener){
                throw new Exception("disconnectListener not config");
            }
        }

        protected function send($content){
            return $this->socket->send($content);
        }

        public function sendPacket(Base $packet){
            if($this->running){
                if(false !== call_user_func_array($this->sendListener,[$packet, $this])){
                    $data = $packet->convertToArray();
                    $data[$this->sessionKey] = $this->sessionId;
                    $packetStr = ConvertUtil::pack($data);
                    return $this->send(pack('N', strlen($packetStr)). $packetStr);
                }
            }
            return false;
        }

        public function auth(){
            if(!$this->account || !$this->password){
                throw new Exception('account or password not config');
            }
            $authPacket = new Auth();
            $authPacket->setAccount($this->account);
            $authPacket->setPassword($this->password);
            $this->sendPacket($authPacket);
        }

        public function isAuthorized(){
            return $this->authorized;
        }

        public function close(){
            $this->running = false;
            $this->socket = null;
        }

        public function reconnect(){
            $this->close();
            $this->connect();
        }

        public function __construct($host, $port, $reconnectInterval){
            $this->host = $host;
            $this->port = $port;
            $this->reconnectInterval = $reconnectInterval;
        }

        public function setUser($account, $password){
            $this->account = $account;
            $this->password = $password;
        }

        public function setPacketListener($packetListener){
            $this->packetListener = $packetListener;
        }

        public function setSendListener($sendListener){
            $this->sendListener = $sendListener;
        }

        public function setErrorListener($errorListener){
            $this->errorListener = $errorListener;
        }

        public function setConnectListener($connectListener){
            $this->connectListener = $connectListener;
        }

        public function setDisconnectListener($disconnectListener){
            $this->disconnectListener = $disconnectListener;
        }
    }

    class ErrorObject{
        const NETWORK_ERROR = 0;
        const RECV_PACKET_CRASHED = 1;
        const RECV_PACKET_TYPE = 2;
        const AUTH_FAILED = 3;
        const SEND_PACKET_VALID = 4;
        public $code;
        public $message;
        public function __construct($code, $message){
            $this->code = $code;
            $this->message = $message;
        }
    }

    class SwooleClient extends Client{

        public function connect(){
            parent::connect();
            if(!$this->running){
                $this->running = true;
                $this->socket = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
                $this->socket->set($this->config);
                $this->socket->on("error", [$this, 'onError']);
                $this->socket->on("close", [$this, 'onClose']);
                $this->socket->on("connect", [$this, 'onConnect']);
                $this->socket->on("receive", [$this, 'onReceive']);
                $this->socket->connect($this->host, $this->port);
            }
        }

        public function onError(swoole_client $cli){
            call_user_func_array($this->errorListener,[new ErrorObject(ErrorObject::NETWORK_ERROR, null), $this]);
        }

        public function onClose(swoole_client $cli){
            call_user_func_array($this->disconnectListener,[$this]);
        }

        public function onConnect(swoole_client $cli){
            $this->auth();
        }

        public function onReceive(swoole_client $cli, $message){
            $packetStr = substr($message,4);
            if(($data = ConvertUtil::unpack($packetStr)) && isset($data[$this->sessionKey], $data['from'], $data['to'], $data['type'], $data['content'])){
                $packet = Base::getPacketFromArray($data);
                if(!$this->authorized){
                    if($packet->type == Auth::TYPE){
                        $authPacket = (new Auth())->getPacketFromPacket($packet);
                        switch($authPacket->getStatus()){
                            case Auth::STATUS_NEED_AUTH:
                                $this->auth();
                                break;
                            case Auth::STATUS_AUTH_FAILED:
                                call_user_func_array($this->errorListener,[new ErrorObject(ErrorObject::AUTH_FAILED, $message), $this]);
                                break;
                            case Auth::STATUS_AUTH_SUCCESS:
                                $this->sessionId = $data[$this->sessionKey];
                                $this->authorized = true;
                                $this->uid = $authPacket->getUid();
                                call_user_func_array($this->connectListener,[$this]);
                                break;
                        }
                    }else{
                        call_user_func_array($this->errorListener,[new ErrorObject(ErrorObject::RECV_PACKET_TYPE, $message), $this]);
                    }
                }else{
                    call_user_func_array($this->packetListener,[$packet, $this]);
                }
            }else{
                call_user_func_array($this->errorListener,[new ErrorObject(ErrorObject::RECV_PACKET_CRASHED, $message), $this]);
            }
        }
    }
}
/********************* END ***************************/