<?php

namespace minions\manager;

use PDO;
use minions\model\LoginLog;
use minions\request\ApiRequest;
use minions\response\ApiResponse;
use minions\response\Code;

class LoginLogManager extends BaseManager {

    const ADD_LOGIN_LOG = 'INSERT INTO `m_login_log`(uid,create_time,ip,status) VALUES(?,?,?,?)';
    const COUNT_BY_STATUS = 'SELECT count(*) FROM `m_login_log` WHERE uid = ? and create_time > ? and status = ?';

    public static function getInstance($class = __CLASS__) {
        return parent::getInstance($class);
    }

    public function addLog(LoginLog $model){

        $model->createTime = time();
        $model->ip = ApiRequest::getRemoteIp();
        if($model->status != LoginLog::STATUS_SUCCESS){
            $model->status = LoginLog::STATUS_FAILED;
        }
        $dbManager = DbManager::getInstance();
        $statement = $dbManager->prepare(self::ADD_LOGIN_LOG);
        $statement->bindValue(1, $model->uid, PDO::PARAM_INT);
        $statement->bindValue(2, $model->createTime, PDO::PARAM_INT);
        $statement->bindValue(3, $model->ip, PDO::PARAM_STR);
        $statement->bindValue(4, $model->status, PDO::PARAM_INT);
        if(!$statement->execute()){
            return ApiResponse::newInstance(Code::FAIL_DATABASE_ERROR,'add login log failed');
        }

        return null;
    }

    public function getStatusCount($uid, $createTime, $status){

        $dbManager = DbManager::getInstance();
        $statement = $dbManager->prepare(self::COUNT_BY_STATUS);
        $statement->bindValue(1, $uid, PDO::PARAM_INT);
        $statement->bindValue(2, $createTime, PDO::PARAM_INT);
        $statement->bindValue(3, $status, PDO::PARAM_STR);

        return false !== $statement->execute() ? $statement->fetchColumn() : false;
    }
}