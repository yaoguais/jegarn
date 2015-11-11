<?php

namespace minions\manager;
use minions\base\Code;
use \minions\base\Singleton;
use \minions\model\User;
use \PDO;

class UserManager extends Singleton {

	const ADD_USER = 'INSERT INTO `m_user`(`username`,`password`,`create_time`,`nick`) VALUES(?,?,?,?);';
	const GET_USER_BY_UID = 'SELECT uid,username,password FROM `m_user` WHERE `uid` = ?;';
	const GET_USER_BY_USERNAME = 'SELECT uid,username,password FROM `m_user` WHERE `usename` = ?;';
	const REMOVE_USER_BY_UID = 'DELETE FROM `m_user` WHERE uid = ?';
	const REMOVE_USER_BY_USERNAME = 'DELETE FROM `m_user` WHERE username = ?';

	public static function getInstance($class = __CLASS__){

		return parent::getInstance($class);
	}

	public function addUser(User $user){

		if(empty($user->username)){
			return Code::USER_NAME_EMPTY;
		}
		$password = $user->password ? $this->enCryptPassword($user->password) : null;
		$dbManager = DbManager::getInstance();
		$statement = $dbManager->prepare(self::ADD_USER);
		$statement->bindValue(1,$user->username, PDO::PARAM_STR);
		$statement->bindValue(2,$password, PDO::PARAM_STR);
		$statement->bindValue(3,time(),PDO::PARAM_INT);
		$statement->bindValue(4,$user->nick,PDO::PARAM_STR);
		if(!$statement->execute()){
			return Code::FAIL_USER_NAME_ALREADY_EXISTS;
		}
		$user->uid = $dbManager->lastInsertId();

		return Code::SUCCESS;
	}

	public function enCryptPassword($password){

		return hash('sha256', $password);
	}

	public function getUser(User $user){

		$dbManager = DbManager::getInstance();
		if($user->uid){
			$statement = $dbManager->prepare(self::GET_USER_BY_UID);
			$statement->bindValue(1, $user->uid, PDO::PARAM_INT);
		}else if($user->username){
			$statement = $dbManager->prepare(self::GET_USER_BY_USERNAME);
			$statement->bindValue(1, $user->username, PDO::PARAM_STR);
		}else{
			return Code::USER_UID_OR_NAME_NOT_EXISTS;
		}
		if(!$statement->execute()){
			return Code::FAIL_DATABASE_ERROR;
		}
		$result = $statement->fetch(PDO::FETCH_ASSOC);
		if(!$result){
			return Code::FAIL_USER_NOT_EXISTS;
		}
		$user->uid = $result['uid'];
		$user->username = $result['username'];
		$user->password = $result['password'];

		return Code::SUCCESS;
	}

	public function authUser(User $input){

		$user = clone $input;
		if($input->password && Code::SUCCESS == $this->getUser($user) && $this->enCryptPassword($input->password) == $user->password ){
			return true;
		}

		return false;
	}

	public function removeUser(User $user){

		$dbManager = DbManager::getInstance();
		if($user->uid){
			$statement = $dbManager->prepare(self::REMOVE_USER_BY_UID);
			$statement->bindValue(1, $user->uid, PDO::PARAM_INT);
		}else if($user->username){
			$statement = $dbManager->prepare(self::REMOVE_USER_BY_USERNAME);
			$statement->bindValue(1, $user->username, PDO::PARAM_STR);
		}else{
			return Code::USER_UID_OR_NAME_NOT_EXISTS;
		}
		if(!$statement->execute()){
			return Code::FAIL_DATABASE_ERROR;
		}

		return Code::SUCCESS;
	}
}