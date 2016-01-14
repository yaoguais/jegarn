<?php

namespace jegarn\manager;

use jegarn\cache\Cache;
use jegarn\packet\Packet;
use jegarn\util\ConvertUtil;

class OfflineMessageManager extends BaseOfflineMessageManager{

    public static function getInstance($class = __CLASS__){
        return parent::getInstance($class);
    }

    protected function getCacheKey($id){
        return 'm_' . $id;
    }
}