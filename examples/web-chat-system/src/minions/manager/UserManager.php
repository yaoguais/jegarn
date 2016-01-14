<?php

namespace minions\manager;
use minions\model\LoginLog;
use minions\http\ApiRequest;
use minions\http\ApiResponse;
use minions\base\Code;
use minions\model\User;
use minions\db\Db;
use minions\util\JegarnUtil;
use PDO;
use minions\util\ConvertUtil;
use minions\util\TextUtil;

class UserManager extends BaseManager {

	const ADD_USER =  'INSERT INTO `m_user`(`username`,`password`,`create_time`,`nick`,`motto`,`avatar`,`token`,`reg_ip`) VALUES(?,?,?,?,?,?,?,?);';
	const GET_USER_BY_UID =  'SELECT id,username,password,token,nick,motto,avatar FROM `m_user` WHERE `id` = ?;';
	const GET_USER_BY_USERNAME =  'SELECT id,username,password,token,nick,motto,avatar FROM `m_user` WHERE `username` = ?;';
	const REMOVE_USER_BY_UID =  'DELETE FROM `m_user` WHERE id = ?';
	const REMOVE_USER_BY_USERNAME =  'DELETE FROM `m_user` WHERE username = ?';
	const USER_COUNT_BY_IP        =  'SELECT count(*) FROM `m_user` WHERE reg_ip = ? and create_time > ?';
	const UPDATE_USER_BY_ID       =  'UPDATE `m_user` SET avatar = ? , nick = ? where id = ?';
	const UPDATE_TOKEN_BY_ID      =  'UPDATE `m_user` SET token = ? where id = ?';

	const PASSWORD_VERSION        = 1;
	const COUNTER_UID             = 1;
	const COUNTER_GROUP_ID        = 1;
	const COUNTER_CHATROOM_ID     = 2;

	protected $authorizedUser;

    /**
     * @return User
     */
	public function getAuthorizedUser(){
		return null === $this->authorizedUser ? null : clone $this->authorizedUser;
	}

	public function setAuthorizedUser(User $user){

		if(null === $this->authorizedUser){
			$this->authorizedUser = $user;
		}
	}

	public static function getInstance($class = __CLASS__){

		return parent::getInstance($class);
	}

	public function addUser(User $model){

		if(empty($model->username)){
			return new ApiResponse(Code::FAIL_EMPTY_ACCOUNT, '');
		}
		if($resp = $this->checkPassword($model->password)){
			return $resp;
		}
		$gender = rand(0,10) > 5 ? 'g' : 'b';
		$model->avatar = 'avatar/default/' . $gender . rand(0, 9) . '.jpg';
		$model->token = TextUtil::generateGUID();
        $model->create_time = time();
        $model->reg_ip = ApiRequest::getRemoteIp();
		$password = $this->enCryptPassword($model->password);
		$dbManager = Db::getInstance();
		$statement = $dbManager->prepare(self::ADD_USER);
		if(!$statement->execute([$model->username, $password, $model->create_time, $model->nick, $model->motto, $model->avatar, $model->token,$model->reg_ip])){
			return new ApiResponse(Code::FAIL_USER_NAME_ALREADY_EXISTS,null);
		}
		$model->id = $dbManager->lastInsertId();
		JegarnUtil::addUser($model->id, $model->username, $model->token);
		JegarnUtil::sendUserSystemTextChatMessage($model->id, 'welcome to jegarn');
		// add login record
		$loginLog = new LoginLog($model->id, LoginLog::STATUS_SUCCESS);
		LoginLogManager::getInstance()->addLog($loginLog);

		// make friends with counter, join group 'Counter Group', join chatroom 'Counter Room'
		$targetId = self::COUNTER_UID;
		$groupId = self::COUNTER_GROUP_ID;
		$chatroomId = self::COUNTER_CHATROOM_ID;
		$sqlList = [
			"INSERT INTO `m_roster`(uid,target_id,status,create_time,update_time,remark,group_id,rank) VALUES ({$targetId},{$model->id},3,{$model->create_time},0,NULL,0,0)",
			"INSERT INTO `m_roster`(uid,target_id,status,create_time,update_time,remark,group_id,rank) VALUES ({$model->id},{$targetId},3,{$model->create_time},0,NULL,0,0)",
			"INSERT into `m_group_user`(gid,uid,permission,create_time,status,remark) values($groupId,{$model->id},0,{$model->create_time},3,NULL)",
			"INSERT into `m_group_user`(gid,uid,permission,create_time,status,remark) values($chatroomId,{$model->id},0,{$model->create_time},3,NULL)"
		];
		foreach($sqlList as $sql){
			$dbManager->exec($sql);
		}
		JegarnUtil::joinGroup($groupId, $model->id);
		JegarnUtil::joinChatroom($chatroomId, $model->id);

		return null;
	}

	public function addUserEx(User $model){

		$ip = ApiRequest::getRemoteIp();
		$regex = '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(\.\d{1,3}\.\d{1,3})?$/';
		if(!$ip || !preg_match($regex, $ip)){
			return new ApiResponse(Code::FAIL_INVALID_IP, null);
		}
		$dbManager = Db::getInstance();
		$statement = $dbManager->prepare(self::USER_COUNT_BY_IP);
		if(!$statement->execute([$ip, time() - 86400]) || false === ($number = $statement->fetchColumn())){
			return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'fetch create user count failed' .var_export($statement->errorInfo(),true));
		}
		if($number > 100){
			return new ApiResponse(Code::FAIL_USER_CREATE_TOO_FREQUENTLY, null);
		}

		return $this->addUser($model);
	}

	public function checkPassword($password){

		if(TextUtil::isEmptyString($password)){
			return new ApiResponse(Code::FAIL_EMPTY_PASSWORD, null);
		}
		$len = strlen($password);
		if($len < 6 || $len > 32){
			return new ApiResponse(Code::FAIL_PASSWORD_LENGTH, null);
		}
		$regex = '/^[-0-9a-zA-Z`=\\\[\];\',.\/~!@#$%^&*()_+|{}:"<>?]+$/';
		if(!preg_match($regex,$password)){
			return new ApiResponse(Code::FAIL_INVALID_PASSWORD, null);
		}

		return null;
	}

	public function enCryptPassword($password){

		$hashFunc = 'sha256';
		return self::PASSWORD_VERSION . hash($hashFunc, $password);
	}

	public function getUser(User $model){

		$dbManager = Db::getInstance();
		if($model->id){
			$statement = $dbManager->prepare(self::GET_USER_BY_UID);
			$statement->bindValue(1, $model->id, PDO::PARAM_INT);
		}else if($model->username){
			$statement = $dbManager->prepare(self::GET_USER_BY_USERNAME);
			$statement->bindValue(1, $model->username, PDO::PARAM_STR);
		}else{
			return new ApiResponse(Code::FAIL_USER_UID_OR_NAME_NOT_EXISTS, null);
		}
		if(!$statement->execute()){
			return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'get user failed');
		}
		$result = $statement->fetch(PDO::FETCH_ASSOC);
		if(!$result){
			return new ApiResponse(Code::FAIL_USER_NOT_EXISTS, null);
		}
		ConvertUtil::arrayToObject($result,$model, ['id','username','password','avatar','token','nick','motto']);

		return null;
	}

	/**
	 * @param $uid
	 * @return User|null
	 * @throws \Exception
	 */
	public function getUserById($uid){
		$dbManager = Db::getInstance();
		$statement = $dbManager->prepare(self::GET_USER_BY_UID);
		if(!$statement->execute([$uid])){
			return null;
		}
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$statement->setFetchMode(PDO::FETCH_CLASS, 'minions\model\User');

		return $statement->fetch(PDO::FETCH_CLASS);
	}

	public function updateUser(User $model){

		if(!$model->id){
			return new ApiResponse(Code::FAIL_USER_NOT_EXISTS, 'id is empty');
		}
		$dbManager = Db::getInstance();
		$statement = $dbManager->prepare(self::UPDATE_USER_BY_ID);
		if(!$statement->execute([$model->avatar, $model->nick, $model->id])){
			return new ApiResponse(Code::FAIL_DATABASE_ERROR, null);
		}

		return null;
	}

	public function updateUserToken(User $model){

		if(!$model->id){
			return new ApiResponse(Code::FAIL_USER_NOT_EXISTS, 'id is empty');
		}
		$model->token = TextUtil::generateGUID();
		$dbManager = Db::getInstance();
		$statement = $dbManager->prepare(self::UPDATE_TOKEN_BY_ID);
		if(!$statement->execute([$model->token, $model->id])){
			return new ApiResponse(Code::FAIL_DATABASE_ERROR, 'update user token failed');
		}
		// here update with chat server
		JegarnUtil::updateUser($model->id, $model->username, $model->token);

		return null;
	}

	public function removeUser(User $model){

		$dbManager = Db::getInstance();
		if($model->id){
			$statement = $dbManager->prepare(self::REMOVE_USER_BY_UID);
			$statement->bindValue(1, $model->id, PDO::PARAM_INT);
			// this add to get user's user name
			if(!$model->username){
				if($resp = $this->getUser($model)){
					return $resp;
				}
			}
		}else if($model->username){
			$statement = $dbManager->prepare(self::REMOVE_USER_BY_USERNAME);
			$statement->bindValue(1, $model->username, PDO::PARAM_STR);
		}else{
			return new ApiResponse(Code::FAIL_USER_UID_OR_NAME_NOT_EXISTS,null);
		}
		if(!$statement->execute()){
			return new ApiResponse(Code::FAIL_DATABASE_ERROR, null);
		}
		JegarnUtil::removeUser($model->username);

		return null;
	}

	public function auth(User $model, User $input){

		return $this->enCryptPassword($input->password) == $model->password;
	}

	public function login(User $model){

		if(TextUtil::isEmptyString($model->password)){
			return new ApiResponse(Code::FAIL_EMPTY_PASSWORD, null);
		}
		$dbModel = clone $model;
		if($resp = $this->getUser($dbModel)){
			return $resp;
		}
		$loginFailedMax = 5;
		$count = LoginLogManager::getInstance()->getStatusCount($dbModel->id, time() - 1200, LoginLog::STATUS_FAILED);
		if(false === $count || $count >= $loginFailedMax){
			return new ApiResponse(Code::FAIL_LOGIN_FAILED,['next_time' => 1200, 'retry_number' => 0]);
		}
		if(!$this->auth($dbModel, $model)){
			$loginLog = new LoginLog($dbModel->id, LoginLog::STATUS_FAILED);
			if($resp = LoginLogManager::getInstance()->addLog($loginLog)){
				return $resp;
			}
			return new ApiResponse(Code::FAIL_LOGIN_FAILED,['next_time' => 0, 'retry_number' => $loginFailedMax - $count - 1]);
		}else{
			$loginLog = new LoginLog($dbModel->id, LoginLog::STATUS_SUCCESS);
			if($resp = LoginLogManager::getInstance()->addLog($loginLog)){
				return $resp;
			}
		}
		$this->updateUserToken($dbModel);
		$this->setAuthorizedUser($dbModel);

		return null;
	}
}