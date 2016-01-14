<?php

/**
 * steps:
 * 1. install mysql schema
 * 2. create user 'counter'
 * 3. create groupchat 'Counter Group'
 * 4. create chatroom 'Counter Room'
 * 5. create robot_counter config file
 */

// library minions
require __DIR__ . '/src/bootstrap.php';
// library jegarn
require __DIR__ . '/../../sdk/php/src/jegarn.php';

$configFile = __DIR__ . '/config/application.ini';
if(!file_exists($configFile)){
    echo "config file $configFile lost\n";exit;
}
$lock = __DIR__ . '/config/install.lock';
if(file_exists($lock)){
    echo "delete file $lock force to reinstall\n";exit;
}
echo "usage: php install.php\n";
echo "before run this script, you should config application.ini, start mysql and redis server.\n\n";
// init mysql
$config = new \Yaf\Config\Ini($configFile, 'product');
minions\db\Db::getInstance()->initConfig($config->get('application')->get('database'));
$cacheConfig = $config->get('application')->get('cache');
jegarn\cache\Cache::getInstance()->initConfig($cacheConfig);
$chatConfig = $config->get('application')->get('chat');

$db = minions\db\Db::getInstance();
$cache = jegarn\cache\Cache::getInstance();

$schemaFile = __DIR__ . '/install/mysql.sql';
$sqlList = explode(";\n", file_get_contents($schemaFile));
//STEP 1
echo "step1: install schemas\n";
foreach($sqlList as $sql){
    if(substr($sql,0,2) == '--' || '' == trim($sql)){
        continue;
    }
    if(false === $db->exec($sql)){
        echo "execute sql failed, exit\n";exit;
    }else if(preg_match('/CREATE\s+TABLE\s+(.+)\(/', $sql, $match)){
        echo "install table ",trim($match[1],"\r\n `")," ... success\n";
    }
}

// STEP 2
echo "\n","step2: create user 'counter'\n";
$uid = 1; $account = 'counter';
$desc = 'what is the number of infinite?';
$avatar = 'group/default/counter.jpg';
$password = $token = substr(md5(microtime(true)),0,24);
$createTime = time();

$sql = "INSERT INTO `m_user`(`id`,`username`,`password`,`create_time`,`nick`,`motto`,`avatar`,`token`,`reg_ip`)";
$sql .= " VALUES($uid,'$account','$password',$createTime,'Counter','$desc','$avatar','$token','127.0.0.1');";
if(false === $db->exec($sql)){
    echo "execute sql failed, exit\n";exit;
}else{
    echo "insert user data ... success\n";
}
// register for jegarn
if(false === jegarn\manager\UserManager::getInstance()->addUser($uid, $account, $token)){
    echo "execute register user failed, exit\n";exit;
}else{
    echo "register user ... success\n";
}

// STEP 3
echo "\n","step3: create groupchat 'Counter Group'\n";
$gid = 1;
$sql = "insert into `m_group`(`id`,`uid`,`type`,`name`,`create_time`,`description`,`icon`)";
$sql .= " VALUES($gid,$uid,0,'Counter Group',$createTime,'$desc','$avatar')";
if(false === $db->exec($sql)){
    echo "execute sql failed, exit\n";exit;
}else{
    echo "insert group data ... success\n";
}
$sql = "insert into `m_group_user`(id,gid,uid,permission,create_time,status,remark) values(1,$gid,$uid,2,$createTime,3,NULL)";
if(false === $db->exec($sql)){
    echo "execute sql failed, exit\n";exit;
}
if(false === jegarn\manager\GroupManager::getInstance()->addGroupUser($gid, $uid)){
    echo "execute register group failed, exit\n";exit;
}else{
    echo "register group ... success\n";
}

// STEP 4
echo "\n","step4: create chatroom 'Counter Room'\n";
$gid = 2;
$sql = "insert into `m_group`(`id`,`uid`,`type`,`name`,`create_time`,`description`,`icon`)";
$sql .= " VALUES($gid,$uid,1,'Counter Room',$createTime,'$desc','$avatar')";
if(false === $db->exec($sql)){
    echo "execute sql failed, exit\n";exit;
}else{
    echo "insert chatroom data ... success\n";
}
$sql = "insert into `m_group_user`(id,gid,uid,permission,create_time,status,remark) values(2,$gid,$uid,2,$createTime,3,NULL)";
if(false === $db->exec($sql)){
    echo "execute sql failed, exit\n";exit;
}
if(false === jegarn\manager\ChatroomManager::getInstance()->addChatroomUser($gid, $uid)){
    echo "execute register chatroom failed, exit\n";exit;
}else{
    echo "register chatroom ... success\n";
}

// STEP 5
echo "\n","step5: create robot_counter config file\n";
$robotCounterConfigFile = __DIR__ . '/config/robot_counter.php';
$tpl = <<<EOF
<?php

return [
    'cache'       => [
        'host'     => '{$cacheConfig['host']}',
        'port'     => {$cacheConfig['port']},
        'timeout'  => {$cacheConfig['timeout']},
        'password' => '{$cacheConfig['password']}'
    ],
    'server' => [
        'host' => '{$chatConfig['remoteAddress']}',
        'port' => {$chatConfig['remotePort']},
        'reconnectInterval' => 5000
    ],
    'user' => [
        'account' => '{$account}',
        'password' => '{$token}'
    ],
    'client' => [
        'open_length_check'     => 1,
        'package_length_type'   => 'N',
        'package_length_offset' => 0,
        'package_body_offset'   => 4,
        'package_max_length'    => 2048,
        'socket_buffer_size'    => 1024 * 1024 * 2,
        'daemonize'             => 1
    ]
];
EOF;
file_put_contents($robotCounterConfigFile,$tpl);
touch($lock);

// FINISH
echo "\n","install success, welcome to jegarn!\n\n";