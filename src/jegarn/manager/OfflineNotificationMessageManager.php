<?php

namespace jegarn\manager;

use jegarn\cache\Cache;
use jegarn\util\ConvertUtil;

class OfflineNotificationMessageManager extends BaseOfflineMessageManager{

    public static function getInstance($class = __CLASS__){
        return parent::getInstance($class);
    }

    protected function getCacheKey($id){
        return 'N_' . $id;
    }
}