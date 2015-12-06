<?php

namespace minions\manager;
use minions\model\LoginLog;
use minions\request\ApiRequest;
use minions\response\ApiResponse;
use minions\response\Code;
use \minions\model\User;
use \PDO;
use \minions\util\Convert;
use \minions\util\Text;

class UserManager extends BaseManager {

	const ADD_USER = 'INSERT INTO `m_user`(`username`,`password`,`create_time`,`nick`,`avatar`,`token`,`reg_ip`) VALUES(?,?,?,?,?,?,?);';
	const GET_USER_BY_UID = 'SELECT id,username,password,token,nick,avatar FROM `m_user` WHERE `id` = ?;';
	const GET_USER_BY_USERNAME = 'SELECT id,username,password FROM `m_user` WHERE `username` = ?;';
	const REMOVE_USER_BY_UID = 'DELETE FROM `m_user` WHERE id = ?';
	const REMOVE_USER_BY_USERNAME = 'DELETE FROM `m_user` WHERE username = ?';
	const USER_COUNT_BY_IP        = 'SELECT count(*) FROM `m_user` WHERE reg_ip = ? and create_time > ?';
	const UPDATE_USER_BY_ID       = 'UPDATE `m_user` SET avatar = ? , nick = ? where id = ?';
	const UPDATE_TOKEN_BY_ID      = 'UPDATE `m_user` SET token = ? where id = ?';

	protected $authorizedUser;

	public function getAuthorizedUser(){

		return null === $this->authorizedUser ? null : clone $this->authorizedUser;
	}

	public function setAuthorizedUser($user){

		if(null === $this->authorizedUser){
			$this->authorizedUser = $user;
		}
	}

	public static function getInstance($class = __CLASS__){

		return parent::getInstance($class);
	}

	public function addUser(User $user){

		if(empty($user->username)){
			return ApiResponse::newInstance(Code::FAIL_EMPTY_ACCOUNT, '');
		}
		if($resp = $this->checkPassword($user->password)){
			return $resp;
		}
		$user->token = Text::generateGUID();
		$password = $this->enCryptPassword($user->password);
		$dbManager = DbManager::getInstance();
		$statement = $dbManager->prepare(self::ADD_USER);
		$statement->bindValue(1,$user->username, PDO::PARAM_STR);
		$statement->bindValue(2,$password, PDO::PARAM_STR);
		$statement->bindValue(3,time(),PDO::PARAM_INT);
		$statement->bindValue(4,$user->nick,PDO::PARAM_STR);
		$statement->bindValue(5,$user->avatar, PDO::PARAM_STR);
		$statement->bindValue(6,$user->token, PDO::PARAM_STR);
		$statement->bindValue(7, ApiRequest::getRemoteIp(), PDO::PARAM_STR);
		if(!$statement->execute()){
			return ApiResponse::newInstance(Code::FAIL_USER_NAME_ALREADY_EXISTS,null);
		}
		$user->id = $dbManager->lastInsertId();

		return null;
	}

	public function addUserEx(User $user){

		$ip = ApiRequest::getRemoteIp();
		$regex = '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(\.\d{1,3}\.\d{1,3})?$/';
		if(!$ip || !preg_match($regex, $ip)){
			return ApiResponse::newInstance(Code::FAIL_INVALID_IP, null);
		}
		$dbManager = DbManager::getInstance();
		$statement = $dbManager->prepare(self::USER_COUNT_BY_IP);
		$statement->bindValue(1, $ip, PDO::PARAM_STR);
		$statement->bindValue(2, time()-86400,PDO::PARAM_INT);
		if(!$statement->execute() || false === ($number = $statement->fetchColumn())){
			return ApiResponse::newInstance(Code::FAIL_DATABASE_ERROR, 'fetch create user count failed');
		}
		if($number > 100){
			return ApiResponse::newInstance(Code::FAIL_USER_CREATE_TOO_FREQUENTLY, null);
		}

		return $this->addUser($user);
	}

	public function checkPassword($password){

		if(Text::isEmptyString($password)){
			return ApiResponse::newInstance(Code::FAIL_EMPTY_PASSWORD, null);
		}
		$len = strlen($password);
		if($len < 6 || $len > 32){
			return ApiResponse::newInstance(Code::FAIL_PASSWORD_LENGTH, null);
		}
		$regex = '/^[-0-9a-zA-Z`=\\\[\];\',.\/~!@#$%^&*()_+|{}:"<>?]+$/';
		if(!preg_match($regex,$password)){
			return ApiResponse::newInstance(Code::FAIL_INVALID_PASSWORD, null);
		}

		return null;
	}

	public function enCryptPassword($password){

		return hash('sha256', $password);
	}

	public function getUser(User $user){

		$dbManager = DbManager::getInstance();
		if($user->id){
			$statement = $dbManager->prepare(self::GET_USER_BY_UID);
			$statement->bindValue(1, $user->id, PDO::PARAM_INT);
		}else if($user->username){
			$statement = $dbManager->prepare(self::GET_USER_BY_USERNAME);
			$statement->bindValue(1, $user->username, PDO::PARAM_STR);
		}else{
			return ApiResponse::newInstance(Code::FAIL_USER_UID_OR_NAME_NOT_EXISTS, null);
		}
		if(!$statement->execute()){
			return ApiResponse::newInstance(Code::FAIL_DATABASE_ERROR, 'get user failed');
		}
		$result = $statement->fetch(PDO::FETCH_ASSOC);
		if(!$result){
			return ApiResponse::newInstance(Code::FAIL_USER_NOT_EXISTS, null);
		}
		Convert::arrayToObject($result,$user, ['id','username','password','avatar','token']);

		return null;
	}

	public function updateUser(User $user){

		if(!$user->id){
			return ApiResponse::newInstance(Code::FAIL_USER_NOT_EXISTS, 'id is empty');
		}
		$dbManager = DbManager::getInstance();
		$statement = $dbManager->prepare(self::UPDATE_USER_BY_ID);
		$statement->bindValue(1, $user->avatar, PDO::PARAM_STR);
		$statement->bindValue(2, $user->nick, PDO::PARAM_STR);
		$statement->bindValue(3, $user->id, PDO::PARAM_INT);
		if(!$statement->execute()){
			return ApiResponse::newInstance(Code::FAIL_DATABASE_ERROR, null);
		}

		return null;
	}

	public function updateUserToken(User $user){

		if(!$user->id){
			return ApiResponse::newInstance(Code::FAIL_USER_NOT_EXISTS, 'id is empty');
		}
		$user->token = Text::generateGUID();
		$dbManager = DbManager::getInstance();
		$statement = $dbManager->prepare(self::UPDATE_TOKEN_BY_ID);
		$statement->bindValue(1, $user->token, PDO::PARAM_STR);
		$statement->bindValue(2, $user->id, PDO::PARAM_INT);
		if(!$statement->execute()){
			return ApiResponse::newInstance(Code::FAIL_DATABASE_ERROR, 'update user token failed');
		}

		return null;
	}

	public function removeUser(User $user){

		$dbManager = DbManager::getInstance();
		if($user->id){
			$statement = $dbManager->prepare(self::REMOVE_USER_BY_UID);
			$statement->bindValue(1, $user->id, PDO::PARAM_INT);
		}else if($user->username){
			$statement = $dbManager->prepare(self::REMOVE_USER_BY_USERNAME);
			$statement->bindValue(1, $user->username, PDO::PARAM_STR);
		}else{
			return ApiResponse::newInstance(Code::FAIL_USER_UID_OR_NAME_NOT_EXISTS,null);
		}
		if(!$statement->execute()){
			return ApiResponse::newInstance(Code::FAIL_DATABASE_ERROR, null);
		}

		return null;
	}

	public function login(User $input){

		if(Text::isEmptyString($input->password)){
			return ApiResponse::newInstance(Code::FAIL_EMPTY_PASSWORD, null);
		}
		$user = clone $input;
		if($resp = $this->getUser($user)){
			return $resp;
		}
		$loginFailedMax = 5;
		$count = LoginLogManager::getInstance()->getStatusCount($user->id, time() - 1200, LoginLog::STATUS_FAILED);
		if(false === $count || $count >= $loginFailedMax){
			return ApiResponse::newInstance(Code::FAIL_LOGIN_FAILED,['next_time' => 1200, 'retry_number' => 0]);
		}
		if($this->enCryptPassword($input->password) != $user->password){
			$loginLog = new LoginLog($input->id, LoginLog::STATUS_FAILED);
			LoginLogManager::getInstance()->addLog($loginLog);
			return ApiResponse::newInstance(Code::FAIL_LOGIN_FAILED,['next_time' => 0, 'retry_number' => $loginFailedMax - $count - 1]);
		}
		$this->updateUserToken($user);
		$this->setAuthorizedUser($user);

		return null;
	}
}