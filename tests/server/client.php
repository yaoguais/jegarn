<?php
use jegarn\packet\Packet;
use jegarn\packet\AuthPacket;
use jegarn\packet\TextChatPacket;
use jegarn\packet\TextChatroomPacket;
use jegarn\packet\TextGroupChatPacket;
use jegarn\util\ConvertUtil;

require __DIR__ . '/../../src/bootstrap.php';
require __DIR__ . '/module/initData.php';
if($argc !== 6){
    echo "example: php client.php server_host server_port account password friend_uid\n";
    echo "available users:\n";
    printf("%-8s%-12s%-8s\n", 'uid', 'account', 'password');
    foreach($users as $u){
        printf("%-8s%-12s%-8s\n", $u['uid'], $u['account'], $u['password']);
    }
    echo "before test, you must run shell './init.sh' to make the users, group and chatroom\n";
    echo "after test, you should run shell './destroy.sh' to clear the init data\n";
    echo "may you success !\n\n";
    exit;
}
$host = $argv[1];
$port = $argv[2];
G::$account = $argv[3];
G::$password = $argv[4];
G::$friendId = $argv[5];
G::$uid = 0;
G::$groupId = $groupId;
G::$chatroomId = $chatroomId;
G::$auth = false;
G::$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
$client = G::$client;
$client->set(array(
    'open_length_check'     => 1,
    'package_length_type'   => 'N',
    'package_length_offset' => 0,
    'package_body_offset'   => 4,
    'package_max_length'    => 2048,
    'socket_buffer_size' => 1024 * 1024 * 2
));
/** @noinspection PhpUnusedParameterInspection */
$client->on("error", function(swoole_client $cli){
    echo "error\n";
});
/** @noinspection PhpUnusedParameterInspection */
$client->on("close", function(swoole_client $cli){
    echo "Connection close\n";
});
$client->on("connect", function(swoole_client $cli) {
    // send a auth packet
    $packet = new AuthPacket();
    $packet->setFrom(0);
    $packet->setTo('system');
    $packet->setAccount(G::$account);
    $packet->setPassword(G::$password);
    $cli->send(serializePacket($packet));
});
$client->on("receive", function(swoole_client $cli, $message){
    //echo 'receive: ',$message,"\n";
    if(($data = substr($message,4)) && ($result = ConvertUtil::unpack($data)) && isset($result['from']) && isset($result['to'])
        && isset($result['type']) && isset($result['content'])){
        G::$sessionId = isset($result['session_id']) ? $result['session_id'] : null;
        /* @var Packet $packet */
        $packet = Packet::getPacketFromArray($result);
        if(!G::$auth){
            $authPacket = new AuthPacket();
            $authPacket->setFrom(G::$account);
            $authPacket->setTo('system');
            if($packet->getType() == $authPacket->getType()){
                $authPacket->setContent($packet->getContent());
                switch($authPacket->getStatus()){
                    case AuthPacket::STATUS_NEED_AUTH:
                        $authPacket->setAccount(G::$account);
                        $authPacket->setPassword(G::$password);
                        $cli->send(serializePacket($authPacket));
                    break;
                    case AuthPacket::STATUS_AUTH_FAILED:
                        echo "error: auth failed\n";
                    break;
                    case AuthPacket::STATUS_AUTH_SUCCESS:
                        echo "auth success\n";
                        G::$auth = true;
                        G::$uid = $authPacket->getUid();
                        sendChatPacket();
                        sendGroupMessage();
                        sendChatroomMessage();
                    break;
                    default:
                        echo "error: auth status undefined!\n";
                        exit;
                }
            }else{
                echo "error: first message from server is not auth message!\n";
                exit;
            }
        }
        echo "---------RECEIVE----------------------------\n";
        echo "from: ",$packet->getFrom(),"\n";
        echo "to: ",$packet->getTo(),"\n";
        echo "type: ",$packet->getType(),"\n";
        echo "session: ",G::$sessionId,"\n";
        echo "content: ",json_encode($packet->getContent(), JSON_UNESCAPED_UNICODE),"\n\n";
    }else{
        echo "receive error: $message\n";
    }
});
/*
swoole_timer_add(1000*240, function($interval) {
    if($interval != 1000*240){
        return;
    }
    echo "will send chat message:",time(),"\n";
    if(G::$auth){

    }
});

swoole_timer_add(1000*120, function($interval) {
    if($interval != 1000*120){
        return;
    }
    echo "will send normal group message:",time(),"\n";
    if(G::$auth){

    }
});

swoole_timer_add(1000*180, function($interval) {
    if($interval != 1000*180){
        return;
    }
    echo "will send chatroom message:",time(),"\n";
    if(G::$auth){

    }
});*/


$client->connect($host, $port);
class G{
    /* @var swoole_client */
    public static $client;
    public static $uid;
    public static $account;
    public static $password;
    public static $auth;
    public static $sessionId;
    public static $friendId;
    public static $groupId;
    public static $chatroomId;
}
function serializePacket(Packet $packet){
    $data = ['from' => $packet->getFrom(), 'to' => $packet->getTo(), 'type' => $packet->getType(), 'content' => $packet->getContent()];
    $data['session_id'] = G::$sessionId;
    echo "-----------SEND--------------------------------\n";
    echo 'send: ',json_encode($data, JSON_UNESCAPED_UNICODE),"\n";
    $data = ConvertUtil::pack($data);
    $data =  pack('N', strlen($data)) . $data;
    echo "packet length: ",strlen($data)."\n\n";
    return $data;
}

function sendChatPacket(){
    // send friend a hello message
    $textMessage = new TextChatPacket();
    $textMessage->setFrom(G::$uid);
    $textMessage->setTo(G::$friendId);
    $textMessage->setText('hello, how are you?[ID:'.rand(1000,9999).']');
    G::$client->send(serializePacket($textMessage));
}

function sendGroupMessage(){
    // send a normal group message
    $textGroupMessage = new TextGroupChatPacket();
    $textGroupMessage->setFrom(G::$uid);
    $textGroupMessage->setGroupId(G::$groupId);
    $textGroupMessage->setSendToAll();
    $textGroupMessage->setText('everybody is ok?[ID:'.rand(1000,9999).']');
    G::$client->send(serializePacket($textGroupMessage));
    // send to friend
    $textGroupMessage = new TextGroupChatPacket();
    $textGroupMessage->setFrom(G::$uid);
    $textGroupMessage->setGroupId(G::$groupId);
    $textGroupMessage->setTo(G::$friendId);
    $textGroupMessage->setText('is ok, friend?[ID:'.rand(1000,9999).']');
    G::$client->send(serializePacket($textGroupMessage));
}

function sendChatroomMessage(){
    // send a chatroom message
    $textGroupMessage = new TextChatroomPacket();
    $textGroupMessage->setFrom(G::$uid);
    $textGroupMessage->setGroupId(G::$chatroomId);
    $textGroupMessage->setText('everybody is ok?[ID:'.rand(1000,9999).']');
    G::$client->send(serializePacket($textGroupMessage));
}