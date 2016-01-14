<?php

namespace jegarn\listener;

use jegarn\packet\Packet;
use jegarn\session\Session;
use jegarn\manager\OfflineMessageManager;
use jegarn\manager\ServerManager;
use jegarn\manager\SessionManager;
use jegarn\packet\ChatPacket;
use jegarn\log\Logger;


class ChatPacketListener implements PacketInterface{

    public function dispatchReceivePacket(Packet $packet, Session $session) {
        /* @var \jegarn\session\SwooleSession $toUserSession
         * @var ChatPacket $pkt
         */
        if($session->isAuth()){
            if($pkt = ChatPacket::getPacketFromPacket($packet)){
                if($toUserSession = SessionManager::getInstance()->getSessionByUserId($packet->getTo())){
                    Logger::addInfo('user online,chat(uid:'.$packet->getTo().')');
                    ServerManager::getInstance()->send($toUserSession, $pkt);
                }else{
                    Logger::addInfo('user offline,storage chat(uid:'.$packet->getTo().')');
                    OfflineMessageManager::getInstance()->addPacket($packet->getTo(), $pkt);
                }
            }
        }

    }

    public function dispatchSendPacket(Packet $packet, Session $toUserSession){
        /* @var \jegarn\session\SwooleSession $toSession
         * @var ChatPacket $pkt
         */
        if($pkt = ChatPacket::getPacketFromPacket($packet)){
            // user connection is lost, but session is stay, session test user online but socket not available, so save the packet
            if(!$toUserSession->isReachable() || !$toUserSession->isAuth()){
                OfflineMessageManager::getInstance()->addPacket($packet->getTo(), $pkt);
            }
        }
    }

    public function dispatchOfflinePacket(Session $session){
        // if(!$session->isAuth() || !$session->getUserId()) return; // before dispatch, both two is checked
        $omm = OfflineMessageManager::getInstance();
        $uid = $session->getUserId();
        $sm = ServerManager::getInstance();
        /* @var ChatPacket $packet */
        while(($packet = $omm->getPacket($uid)) && $packet instanceof ChatPacket){
            $sm->send($session, $packet);
        }
    }
}