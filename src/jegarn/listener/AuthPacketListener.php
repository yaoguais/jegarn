<?php

namespace jegarn\listener;

use jegarn\packet\Packet;
use jegarn\session\Session;
use jegarn\manager\ServerManager;
use jegarn\manager\SessionManager;
use jegarn\packet\AuthPacket;
use jegarn\log\Logger;
use jegarn\util\TextUtil;


class AuthPacketListener implements PacketInterface{

    public function dispatchReceivePacket(Packet $packet, Session $session) {
        if($session->isAuth()) return true;
        /* @var \jegarn\session\SwooleSession $session
         * @var AuthPacket $pkt
         */
        if($pkt = AuthPacket::getPacketFromPacket($packet)){
            if($pkt->auth()){
                $session->setUserId($pkt->getUid());
                $session->setAuth(true);
                $session->setSessionId(TextUtil::generateGUID());
                SessionManager::getInstance()->addSession($session);
                $pkt->setStatus(AuthPacket::STATUS_AUTH_SUCCESS);
            }else{
                $pkt->setStatus(AuthPacket::STATUS_AUTH_FAILED);
                Logger::addInfo('auth failed(fd:'.$session->getFd().')');
            }
        }else{
            $pkt = new AuthPacket();
            $pkt->setPacket($packet);
            $pkt->setStatus(AuthPacket::STATUS_NEED_AUTH);
            Logger::addInfo('auth needed(fd:'.$session->getFd().')');
        }
        ServerManager::getInstance()->send($session, $pkt->getReversedPacket());
        return false;
    }

    public function dispatchSendPacket(Packet $packet, Session $toUserSession){

    }

    public function dispatchOfflinePacket(Session $session){

    }
}