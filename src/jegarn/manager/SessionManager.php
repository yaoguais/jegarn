<?php

namespace jegarn\manager;

use jegarn\session\Session;
use jegarn\listener\SessionInterface;
use jegarn\cache\Cache;
use jegarn\util\ConvertUtil;

class SessionManager extends BaseManager{

    protected $listeners;
    protected $fdSessionInfoMap;// fd => $userId."\t".$sessionId
    protected $fdNewSessionMap;

    public static function getInstance($class = __CLASS__){
        return parent::getInstance($class);
    }

    public function filterSession(Session $session){
        $this->fdNewSessionMap[$session->getFd()] = true;
        return $this->triggerEvent('filterSession', $session);
    }

    public function isNewSession(Session $session){
        return isset($this->fdNewSessionMap[$session->getFd()]);
    }

    public function clearNewSession(Session $session){
        unset($this->fdNewSessionMap[$session->getFd()]);
    }

    /*
     * add user session, session only can be reach by current process, so cache it
     */
    public function addSession(Session $session){
        if($this->isValidSession($session)){
            $cache = Cache::getInstance();
            $sessionStr = ConvertUtil::pack($session);
            // check is there has last dirty session
            if($dirty = $this->getSessionByUserId($session->getUserId())){
                $cache->del($this->getSessionIdCacheKey($dirty->getSessionId()), $this->getUserIdCacheKey($dirty->getUserId()));
            }
            $cache->set($this->getUserIdCacheKey($session->getUserId()), $sessionStr);
            $cache->set($this->getSessionIdCacheKey($session->getSessionId()), $sessionStr);
            // cache session
            $this->fdSessionInfoMap[$session->getFd()] = $session->getUserId().' '.$session->getSessionId();
            return $this->triggerEvent('addSession', $session);
        }
        return null;
    }

    /*
     * remove user session, session may be not cache by this process
     */
    public function removeSession(Session $session){
        if($this->isValidSession($session)){
            $fs = $session;
        }else{
            if($session->getUserId()){
                $fs = $this->getSessionByUserId($session->getUserId());
            }else if($session->getSessionId()){
                $fs = $this->getSessionBySessionId($session->getSessionId());
            }else{
                return null;
            }
        }
        if($fs){
            Cache::getInstance()->del($this->getSessionIdCacheKey($fs->getSessionId()), $this->getUserIdCacheKey($fs->getUserId()));
            return $this->triggerEvent('removeSession', $session);
        }
        return null;
    }

    public function removeSessionByFd($fd){
        if(isset($this->fdSessionInfoMap[$fd])){
            $sessionInfo = $this->fdSessionInfoMap[$fd];
            list($userId, $sessionId) = explode(' ', $sessionInfo);
            Cache::getInstance()->del($this->getSessionIdCacheKey($sessionId), $this->getUserIdCacheKey($userId));
            unset($this->fdSessionInfoMap[$fd]);
        }
    }

    /**
     * @param $userId
     * @return Session|null
     * @throws \Exception
     */
    public function getSessionByUserId($userId){
        if($userId && ($sessionStr = Cache::getInstance()->get($this->getUserIdCacheKey($userId)))){
            if(($foundSession = ConvertUtil::unpack($sessionStr)) && $foundSession instanceof Session){
                return $foundSession;
            }
        }
        return null;
    }

    /**
     * @param $sessionId
     * @return Session|null
     * @throws \Exception
     */
    public function getSessionBySessionId($sessionId){
        if($sessionId && ($sessionStr = Cache::getInstance()->get($this->getSessionIdCacheKey($sessionId)))){
            if(($foundSession = ConvertUtil::unpack($sessionStr)) && $foundSession instanceof Session){
                return $foundSession;
            }
        }
        return null;
    }

    public function isValidSession(Session $session){
        return null !== $session && $session->getFd() && $session->getUserId() && $session->getSessionId();
    }

    public function addListener(SessionInterface $listener){
        if($this->hasListener($listener)){
            $this->removeListener($listener);
        }
        $this->listeners[] = $listener;
    }

    public function removeListener(SessionInterface $listener){
        if($this->listeners){
            foreach($this->listeners as $k => &$l){
                if($listener === $l){
                    unset($this->listeners[$k]);
                }
            }
        }
    }

    public function hasListener(SessionInterface $listener){
        if($this->listeners){
            foreach($this->listeners as &$l){
                if($listener === $l){
                    return true;
                }
            }
        }
        return false;
    }

    protected function triggerEvent($func, Session $session){
        if($this->listeners){
            /* @var SessionInterface $listener */
            foreach($this->listeners as &$listener){
                if(false === $listener->$func($session)){
                    return false;
                }
            }
        }
        return null;
    }

    protected function getSessionIdCacheKey($id){
        return 's_' . $id;
    }

    protected function getUserIdCacheKey($id){
        return 'S_' . $id;
    }
}