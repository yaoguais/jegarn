<?php

namespace minions\manager;

use minions\model\LoginLog;
use minions\http\ApiRequest;
use minions\http\ApiResponse;
use minions\base\Code;
use minions\db\Db;
use PDO;

class LoginLogManager extends BaseManager {

    const ADD_LOGIN_LOG =  'INSERT INTO `m_login_log`(uid,create_time,ip,status) VALUES(?,?,?,?)';
    const COUNT_BY_STATUS =  'SELECT count(*) FROM `m_login_log` WHERE uid = ? and create_time > ? and status = ?';
    const GET_LATEST_USER = 'SELECT distinct uid from `m_login_log` where status=1 order by id desc limit :offset,:limit';

    public static function getInstance($class = __CLASS__) {
        return parent::getInstance($class);
    }

    public function addLog(LoginLog $model){

        $model->createTime = time();
        $model->ip = ApiRequest::getRemoteIp();
        if($model->status != LoginLog::STATUS_SUCCESS){
            $model->status = LoginLog::STATUS_FAILED;
        }
        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::ADD_LOGIN_LOG);
        if(!$statement->execute([$model->uid, $model->createTime, $model->ip, $model->status])){
            return new ApiResponse(Code::FAIL_DATABASE_ERROR,'add login log failed'.var_export($statement->errorInfo(),true));
        }

        return null;
    }

    public function getStatusCount($uid, $createTime, $status){

        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::COUNT_BY_STATUS);

        return false !== $statement->execute([$uid, $createTime, $status]) ? $statement->fetchColumn() : false;
    }

    public function getLatestSuccessUser($offset, $limit){

        $dbManager = Db::getInstance();
        $statement = $dbManager->prepare(self::GET_LATEST_USER);
        $statement->bindValue(':offset',$offset, PDO::PARAM_INT);
        $statement->bindValue(':limit',$limit, PDO::PARAM_INT);
        if(!$statement->execute()){
            return null;
        }
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        if(!$result){
            return null;
        }
        $uidList = [];
        foreach($result as $row){
            $uidList[] = $row['uid'];
        }
        return $uidList;
    }
}