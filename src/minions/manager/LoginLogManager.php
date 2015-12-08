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
        if(!$statement->execute([$model->uid, $model->createTime, $model->ip, $model->status])){
            return ApiResponse::newInstance(Code::FAIL_DATABASE_ERROR,'add login log failed');
        }

        return null;
    }

    public function getStatusCount($uid, $createTime, $status){

        $dbManager = DbManager::getInstance();
        $statement = $dbManager->prepare(self::COUNT_BY_STATUS);

        return false !== $statement->execute([$uid, $createTime, $status]) ? $statement->fetchColumn() : false;
    }
}