<?php

namespace jegarn\listener;

use jegarn\manager\OfflineNotificationMessageManager;
use jegarn\packet\NotificationPacket;
use jegarn\packet\Packet;
use jegarn\session\Session;
use jegarn\manager\ServerManager;
use jegarn\manager\SessionManager;
use jegarn\packet\ChatPacket;
use jegarn\log\Logger;


class NotificationPacketListener implements PacketInterface{

    public function dispatchReceivePacket(Packet $packet, Session $session) {
        /* @var \jegarn\session\SwooleSession $toUserSession
         * @var ChatPacket $pkt
         */
        if($session->isAuth()){
            if($pkt = NotificationPacket::getPacketFromPacket($packet)){
                if($toUserSession = SessionManager::getInstance()->getSessionByUserId($packet->getTo())){
                    Logger::addInfo('user online,notification(uid:'.$packet->getTo().')');
                    ServerManager::getInstance()->send($toUserSession, $pkt);
                }else{
                    Logger::addInfo('user offline,storage notification(uid:'.$packet->getTo().')');
                    OfflineNotificationMessageManager::getInstance()->addPacket($packet->getTo(), $pkt);
                }
            }
        }

    }

    public function dispatchSendPacket(Packet $packet, Session $toUserSession){
        /* @var \jegarn\session\SwooleSession $toSession
         * @var ChatPacket $pkt
         */
        if($pkt = NotificationPacket::getPacketFromPacket($packet)){
            // user connection is lost, but session is stay, session test user online but socket not available, so save the packet
            if(!$toUserSession->isReachable() || !$toUserSession->isAuth()){
                OfflineNotificationMessageManager::getInstance()->addPacket($packet->getTo(), $pkt);
            }
        }
    }

    public function dispatchOfflinePacket(Session $session){
        // if(!$session->isAuth() || !$session->getUserId()) return; // before dispatch, both two is checked
        $omm = OfflineNotificationMessageManager::getInstance();
        $uid = $session->getUserId();
        $sm = ServerManager::getInstance();
        /* @var ChatPacket $packet */
        while(($packet = $omm->getPacket($uid)) && $packet instanceof NotificationPacket){
            $sm->send($session, $packet);
        }
    }
}