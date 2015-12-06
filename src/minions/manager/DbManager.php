<?php

namespace minions\manager;
use \Exception;
use \PDO;

class DbManager extends BaseManager {

    /* @var \PDO */
    protected $pdo;
    protected $config;

    /**
     * @param null|string $class
     *
     * @return \PDO
     * @throws Exception
     */
    public static function getInstance($class = __CLASS__){

        /* @var DbManager $instance */
        $instance =  parent::getInstance($class);
        if($instance->config !== null){
            $c = $instance->config;
            $instance->pdo = new PDO($c['dns'], $c['username'], $c['password'],$c['options'] ? : []);
            if(!$instance->pdo){
                throw new Exception('database connect error');
            }
            if(isset($c['commands']) && trim($c['commands']) != ""){
                $commands = explode(';',$c['commands']);
                foreach($commands as $command){
                    $instance->pdo->exec($command);
                }
            }
        }

        return $instance;
    }

    public function initConfig($config){

        $this->config = $config;
    }

    public function __call($name, $arguments){

        return call_user_func_array([$this->pdo, $name], $arguments);
    }
}