<?php

namespace jegarn\manager;

use jegarn\cache\Cache;
use jegarn\packet\Packet;
use jegarn\util\ConvertUtil;

class OfflineGroupMessageManager extends BaseOfflineMessageManager{

    public static function getInstance($class = __CLASS__){
        return parent::getInstance($class);
    }

    protected function getCacheKey($id){
        return 'M_' . $id;
    }
}