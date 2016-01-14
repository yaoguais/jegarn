<?php
use jegarn\manager\ChatroomManager;
use jegarn\manager\GroupManager;
use jegarn\manager\UserManager;
use jegarn\packet\Packet;
use jegarn\packet\AuthPacket;
use jegarn\packet\TextChatPacket;
use jegarn\packet\TextChatroomPacket;
use jegarn\packet\TextGroupChatPacket;
use jegarn\util\ConvertUtil;

require __DIR__ . '/bootstrap.php';

ini_set('default_socket_timeout', -1);
if($argc < 3){
    echo "php benchmark.php host port [startUserId] [userCount] [executeTime]\n";exit(0);
}
Gbl::$host = $argv[1]; Gbl::$port = intval($argv[2]);
Gbl::$uidIndex = isset($argv[3]) ? intval($argv[3]) : 30000;
Gbl::$userCount = isset($argv[4]) ? intval($argv[4]) : 10;
Gbl::$executeTime = isset($argv[5]) ? intval($argv[5]) : 60;
Gbl::init();
// real do
for($i=0; $i < Gbl::$userCount; ++$i){
    $user = Gbl::getUser();
    $socket = new Socket(Gbl::$host, Gbl::$port, $user['uid'], $user['account'], $user['password'], $user['groupId'], $user['chatroomId']);
    if($socket->connect()){
        Gbl::$fpList[] = $socket->fp;
        Gbl::$socketList[(int)$socket->fp] = $socket;
        $socket->sendAuthPacket();
    }else{
        echo "[ERROR] connect error user id {$user['uid']}\n";
    }
}
$second = 15;
echo "wait $second second\n";
sleep($second);
$start = time();
echo "go!!!\n";
while(true){
    $readList = $writeList = $errorList = Gbl::$fpList;
    if(socket_select($readList, $writeList, $errorList, null) > 0){
        if($readList){
            foreach($readList as $fp){
                Gbl::$socketList[(int)$fp]->receive();
            }
        }
        if($writeList){
            foreach($writeList as $fp){
                Gbl::$socketList[(int)$fp]->sendRandomPacket();
            }
        }
    }
    if(time() - $start > Gbl::$executeTime){
        break;
    }
}
echo "time reach\n";



class Gbl{
    public static $host;
    public static $port;
    public static $userCount;
    public static $executeTime;
    public static $uidIndex;
    public static $roomNumber = 5;
    /**
     * @var Socket[]
     */
    public static $socketList;
    public static $fpList;
    public static $uidList;
    public static $groupList;
    public static $chatroomList;
    public static function init(){
        for($i=0; $i< self::$roomNumber;++$i){
            self::$groupList[] = 10000+$i;
            self::$chatroomList[] = 20000+$i;
        }
    }
    protected static function getGroup(){
        return self::$groupList[rand(0, self::$roomNumber - 1)];
    }
    protected static function getChatroom(){
        return self::$chatroomList[rand(0, self::$roomNumber - 1)];
    }
    public static function getRandomUserId(){
        return self::$uidList[rand(0, count(self::$uidList) - 1)];
    }
    public static function getUser(){
        $user = ['uid' => self::$uidIndex, 'account' => 'tester_' . self::$uidIndex, 'password' => rand(100000, 999999),
                 'groupId' => self::getGroup(), 'chatroomId' => self::getChatroom()
                ];
        self::$uidList[] = self::$uidIndex;
        ++self::$uidIndex;
        UserManager::getInstance()->addUser($user['uid'], $user['account'], $user['password']);
        GroupManager::getInstance()->addGroupUser($user['groupId'], $user['uid']);
        ChatroomManager::getInstance()->addChatroomUser($user['chatroomId'], $user['uid']);
        return $user;
    }
}

class Socket {
    /**
     * @var resource
     */
    public $fp;
    protected $host;
    protected $port;
    protected $userId;
    protected $account;
    protected $password;
    protected $groupId;
    protected $chatroomId;
    protected $auth;
    protected $buffer;
    protected $sessionId;
    public function __construct($host, $port, $userId,$account, $password, $groupId, $chatroomId){
        $this->host = $host;
        $this->port = $port;
        $this->userId = $userId;
        $this->account = $account;
        $this->password = $password;
        $this->groupId = $groupId;
        $this->chatroomId = $chatroomId;
        $this->buffer = '';
    }

    public function connect(){
        if($this->fp = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)){
            if(socket_connect($this->fp, $this->host, $this->port)){
                socket_set_nonblock($this->fp);
                return true;
            }
        }
        return false;
    }

    public function isAuth(){
        return $this->auth;
    }

    public function send($data){
        return socket_write($this->fp, $data);
    }

    public function receive(){
        echo "begin to receive message:{$this->userId}\n";
        $data = '';
        while($row = socket_read($this->fp, 128)){
            $data .= $row;
        }
        $this->buffer .= $data;
        $bufferLen = strlen($this->buffer);
        if($bufferLen > 4){
            $lengthInfo = unpack('N', substr($this->buffer, 0, 4));
            if(isset($lengthInfo[1])){
                if($bufferLen >= 4 + $lengthInfo[1]){
                    $message = substr($this->buffer, 4, $lengthInfo[1]);
                    $this->buffer = substr($this->buffer, 4 + $lengthInfo[1]);
                    $this->dealMessage($message);
                }
            }else{
                echo "[ERROR] pack length error\n";
            }
        }
    }

    protected function dealMessage($data){
        echo "begin to deal message\n";
        if($data && ($result = ConvertUtil::unpack($data)) && isset($result['from']) && isset($result['to'])
            && isset($result['type']) && isset($result['content'])){
            $this->sessionId = isset($result['session_id']) ? $result['session_id'] : null;
            /* @var Packet $packet
             * @var AuthPacket $authPacket
             */
            $packet = Packet::getPacketFromArray($result);
            if(!$this->auth){
                if($authPacket = AuthPacket::getPacketFromPacket($packet)){
                    switch($authPacket->getStatus()){
                        case AuthPacket::STATUS_NEED_AUTH:
                            echo "[ERROR] need auth\n";
                            break;
                        case AuthPacket::STATUS_AUTH_FAILED:
                            echo "[ERROR] auth failed\n";
                            break;
                        case AuthPacket::STATUS_AUTH_SUCCESS:
                            echo "[SUCCESS] auth success\n";
                            $this->auth = true;
                            $this->userId = $authPacket->getUid();
                            break;
                        default:
                            echo "[ERROR] auth status undefined!\n";
                    }
                }else{
                    echo "[ERROR] first packet is not auth packet\n";
                }
            }
            echo "---------RECEIVE----------------------------\n";
            echo "from: ",$packet->getFrom(),"\n";
            echo "to: ",$packet->getTo(),"\n";
            echo "type: ",$packet->getType(),"\n";
            echo "session: ",$this->sessionId,"\n";
            echo "content: ",json_encode($packet->getContent(), JSON_UNESCAPED_UNICODE),"\n\n";
        }else{
            echo "[ERROR] receive error\n";
        }
    }

    public function serializePacket(Packet $packet){
        $data = ['from' => $packet->getFrom(), 'to' => $packet->getTo(), 'type' => $packet->getType(), 'content' => $packet->getContent()];
        $data['session_id'] = $this->sessionId;
        echo "-----------SEND--------------------------------\n";
        echo 'send: ', json_encode($data, JSON_UNESCAPED_UNICODE), "\n\n";
        $data = ConvertUtil::pack($data);
        $data = pack('N', strlen($data)) . $data;
        return $data;
    }

    public function sendAuthPacket(){
        $packet = new AuthPacket();
        $packet->setFrom(0);
        $packet->setTo('system');
        $packet->setAccount($this->account);
        $packet->setPassword($this->password);
        $this->send($this->serializePacket($packet));
    }

    public function sendRandomPacket(){
        if($this->auth){
            $functions = ['sendChatPacket', 'sendGroupMessage', 'sendChatroomMessage'];
            $func = $functions[rand(0, count($functions) - 1)];
            $this->$func();
        }
    }

    public function sendChatPacket(){
        // send friend a hello message
        $textMessage = new TextChatPacket();
        $textMessage->setFrom($this->userId);
        $textMessage->setTo(Gbl::getRandomUserId());
        $textMessage->setText('hello, how are you?中文[ID:' . rand(1000, 9999) . ']');
        $this->send($this->serializePacket($textMessage));
    }

    public function sendGroupMessage(){
        // send a normal group message
        $textGroupMessage = new TextGroupChatPacket();
        $textGroupMessage->setFrom($this->userId);
        $textGroupMessage->setGroupId(Gbl::getRandomUserId());
        $textGroupMessage->setSendToAll();
        $textGroupMessage->setText('everybody is ok?汉字[ID:' . rand(1000, 9999) . ']');
        $this->send($this->serializePacket($textGroupMessage));
        // send to friend
        $textGroupMessage = new TextGroupChatPacket();
        $textGroupMessage->setFrom($this->userId);
        $textGroupMessage->setGroupId($this->groupId);
        $textGroupMessage->setTo(Gbl::getRandomUserId());
        $textGroupMessage->setText('is ok, friend?表情[ID:' . rand(1000, 9999) . ']');
        $this->send($this->serializePacket($textGroupMessage));
    }

    public function sendChatroomMessage(){
        // send a chatroom message
        $textGroupMessage = new TextChatroomPacket();
        $textGroupMessage->setFrom($this->userId);
        $textGroupMessage->setGroupId($this->chatroomId);
        $textGroupMessage->setText('everybody is ok?汉语[ID:' . rand(1000, 9999) . ']');
        $this->send($this->serializePacket($textGroupMessage));
    }
}










