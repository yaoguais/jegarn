<?php
/**
 * please see $introduction.
 */

global $introduction;
$introduction = <<<EOF
I am a robot of counter, what?  I am the first member of the system "minions" what's the chat system.
I created a group "Counter Group" and a chatroom "Counter Room" when I was born. And when you join the system,
I will be your friend of roster group "counter" immediately and pull you in the "Counter Group" and  the "Counter Room".
what I do, which is send a number from zero to infinite to you via single chat, groupchat and chatroom.
I will be dead when i reached the infinite.
EOF;

use jegarn\client\ErrorObject;
use jegarn\client\SwooleClient;
use jegarn\manager\UserManager;
use jegarn\packet\Base;
use jegarn\packet\Chat;
use jegarn\packet\Chatroom;
use jegarn\packet\GroupChat;
use jegarn\packet\TextChat;
use jegarn\packet\TextChatroom;
use jegarn\packet\TextGroupChat;
use jegarn\cache\Cache;

require __DIR__ . '/../../sdk/php/src/jegarn.php';
$config = require __DIR__ . '/config/robot_counter.php';

define('COUNTER_UID', 1);
define('COUNTER_GROUP_ID',1);
define('COUNTER_CHATROOM_ID',2);

$infiniteCounterHandler = new InfiniteCounter( __DIR__ . '/logs/infinite_counter.txt');
Cache::getInstance()->initConfig($config['cache']);
$client = new SwooleClient($config['server']['host'], $config['server']['port'], $config['server']['reconnectInterval']);
$client->setConfig($config['client']);
$client->setUser($config['user']['account'], $config['user']['password']);
$client->setConnectListener(function($client){
    echo "connect\n";
});
$client->setDisconnectListener(function($cilent){
    echo "disconnect\n";
});
$client->setErrorListener(function(ErrorObject $errorObject, $client){
    echo 'error code:',$errorObject->code, "\n";
});
$client->setSendListener(function(Base $packet, $client){
    echo "send:\n"; print_r($packet); echo "\n";
});
$client->setPacketListener(function(Base $pkt, $client){
    echo "recv:\n"; print_r($pkt); echo "\n";
    global $introduction;
    switch($pkt->type){
        case Chat::TYPE:
            $packet = new TextChat();
            /* @var TextChat $packet */
            $packet = $packet->getPacketFromPacket($pkt);
            $packet->from = $pkt->to;
            $packet->to = $pkt->from;
            $packet->setText($introduction);
            $client->sendPacket($packet);
            break;
        case GroupChat::TYPE:
            $packet = new TextGroupChat();
            /* @var TextGroupChat $packet */
            $packet = $packet->getPacketFromPacket($pkt);
            $packet->from = $pkt->to;
            $packet->setSendToAll();
            $packet->setText($introduction);
            $client->sendPacket($packet);
            break;
        case Chatroom::TYPE:
            break;
    }
});
swoole_timer_add(30000,function($interval) use($client, $infiniteCounterHandler){
    if($client->isAuthorized()){
        //send online user chat packet
        $uidList = UserManager::getInstance()->getAllOnlineUsers();
        if(count($uidList) > 1){
            $chat = new TextChat();
            $chat->from = COUNTER_UID;
            foreach($uidList as $uid){
                if($uid != COUNTER_UID){
                    $chat->to = $uid;
                    $chat->setText($infiniteCounterHandler->increase());
                    $client->sendPacket($chat);
                }
            }
        }
        /*// send groupchat
        $groupchat = new TextGroupChat();
        $groupchat->from = COUNTER_UID;
        $groupchat->setSendToAll();
        $groupchat->setGroupId(COUNTER_GROUP_ID);
        $groupchat->setText($infiniteCounterHandler->increase());
        $client->sendPacket($groupchat);*/
    }
});
$client->connect();

class InfiniteCounter {

    protected $number = 0;
    protected $storageFile = null;
    protected $numberStr = '';
    const LONG_LONG = 9223372036854775807;

    public function __construct($storageFile){
        $this->storageFile = $storageFile;
        $this->load();
    }

    protected function load(){
        if(file_exists($this->storageFile)){
            $content = file_get_contents($this->storageFile);
            $this->number = intval($content);
            if($this->number >= self::LONG_LONG){
                $this->number = self::LONG_LONG;
                $this->numberStr = $content;
            }
        }else{
            $this->number = 0;
        }
    }

    protected function flush(){
        file_put_contents($this->storageFile, $this->number < self::LONG_LONG ? $this->number : $this->numberStr);
    }

    public function increase(){
        if($this->number < self::LONG_LONG){
            ++$this->number;
            $this->flush();
            return $this->number;
        }else{
            if($this->numberStr === ''){
                $this->numberStr = '9223372036854775807';
                $this->number = self::LONG_LONG;
            }
            // increase
            $this->numberStr = $this->increaseBigNumber($this->numberStr);
            $this->flush();
            return $this->numberStr;
        }
    }

    protected function increaseBigNumber($numberStr){
        $carry = true;
        for($i = strlen($numberStr) - 1; $i >= 0 ; --$i){
            $curValue = ord($numberStr[$i]);
            if($carry && $curValue == 57){
                $numberStr[$i] = '0';
            }else{
                $numberStr[$i] = chr($curValue + 1);
                $carry = false;
                break;
            }
        }
        if($carry){
            $numberStr = '1' . $numberStr;
        }
        return $numberStr;
    }
}
