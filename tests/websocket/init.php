<?php

if($argc != 3){
    echo "php init.php server_host server_port\n";exit;
}
$host = $argv[1];
$port = $argv[2];


require __DIR__ . '/../server/module/initData.php';
$tpl = <<<EOF
<script src="jegarn.js"></script>
<script>
    jegarn.debug = false;
    var globalRecord = {
        host : '{host}',
        port : {port},
        account: '{account}',
        password : '{password}',
        friend_id : {friend_id},
        group_id : {group_id},
        chatroom_id: {chatroom_id}
    };
    var clientInstance = new jegarn.client(globalRecord.host, globalRecord.port, 30 * 1000);
    clientInstance.setUser(globalRecord.account,globalRecord.password);
    clientInstance.setConnectListener(function (s) {
        console.log('connect:', s);
        // send chat message
        var packet = new jegarn.packet.TextChat();
        packet.to = globalRecord.friend_id;
        packet.setText('how old are you? chat'+Math.random());
        s.sendPacket(packet);
        // send friend a groupchat message
        packet = new jegarn.packet.TextGroupChat();
        packet.to = globalRecord.friend_id;
        packet.setGroupId(globalRecord.group_id);
        packet.setText('how old are you? groupchat'+Math.random());
        s.sendPacket(packet);
        // send all group members a message
        packet = new jegarn.packet.TextGroupChat();
        packet.setSendToAll();
        packet.setGroupId(globalRecord.group_id);
        packet.setText('every body is ok? groupchat'+Math.random());
        s.sendPacket(packet);
        // send chatroom message
        packet = new jegarn.packet.TextChatroom();
        packet.setGroupId(globalRecord.chatroom_id);
        packet.setText('what should we chat? chatroom'+Math.random());
        s.sendPacket(packet);
    });
    clientInstance.setDisconnectListener(function (evt, s) {
        console.log('disconnect:', evt, s);
    });
    clientInstance.setErrorListener(function (errorObject, s) {
        console.log('error:', errorObject, s);
    });
    clientInstance.setPacketListener(function (packet, s) {
        console.log('-----------------RECEIVE-------------------');
        console.log('packet:', packet, s);
        console.log('from: ', packet.from);
        console.log('to: ', packet.to);
        console.log('type: ', packet.type);
        console.log('content: ', packet.content);
    });
    clientInstance.setSendListener(function (packet, s) {
        console.log('-----------------SEND-------------------');
        console.log('packet:', packet, s);
        console.log('from: ', packet.from);
        console.log('to: ', packet.to);
        console.log('type: ', packet.type);
        console.log('content: ', packet.content);
    });
    clientInstance.connect();
</script>
EOF;

for($i = 0, $l = count($users); $i < $l; ++$i){
    $file = __DIR__ .'/www/client'.$i.'.html';
    $user = $users[$i];
    $friendUser = $users[($i+1)%$l];
    $content = str_replace([
        '{host}','{port}','{uid}','{account}','{password}','{friend_id}','{group_id}','{chatroom_id}'
    ],[
        $host, $port, $user['uid'], $user['account'], $user['password'], $friendUser['uid'], $groupId, $chatroomId
    ],$tpl);
    file_put_contents($file, $content);
}
echo "success\n";