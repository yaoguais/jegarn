<?php

namespace jegarn\listener;

use jegarn\manager\ChatroomManager;
use jegarn\packet\ChatroomPacket;
use jegarn\packet\Packet;
use jegarn\session\Session;
use jegarn\manager\ServerManager;
use jegarn\manager\SessionManager;
use jegarn\packet\GroupChatPacket;
use jegarn\log\Logger;


class ChatroomPacketListener implements PacketInterface{

    public function dispatchReceivePacket(Packet $packet, Session $session) {
        if(!$session->isAuth()) return;
        /* @var \jegarn\session\SwooleSession $session
         * @var GroupChatPacket $pkt
         */
        if($pkt = ChatroomPacket::getPacketFromPacket($packet)){
            // check send user is a member of this group
            $cm = ChatroomManager::getInstance();
            if($packet->isFromSystemUser() || $cm->isGroupUser($pkt->getGroupId(), $pkt->getFrom())){
                $fromUid = $pkt->getFrom();
                if($uidList = $cm->getGroupUsers($pkt->getGroupId())){
                    $sm = ServerManager::getInstance();
                    foreach($uidList as $uid){
                        if($uid != $fromUid){
                            $pktCopy = clone $pkt;
                            $pktCopy->setTo($uid);
                            if($toSession = SessionManager::getInstance()->getSessionByUserId($uid)){
                                Logger::addInfo('user online,chatroom(uid:'.$uid.')');
                                $sm->send($toSession, $pktCopy);
                            }else{
                                Logger::addInfo('user not online,chatroom,abandon message(uid:'.$uid.')');
                            }
                        }
                    }
                }else{
                    Logger::addInfo('chatroom ' . $pkt->getGroupId() . ' users lost, from uid ' . $pkt->getFrom());
                }
            }else{
                Logger::addInfo('chatroom '.$pkt->getGroupId().' has no user '.$pkt->getFrom(). ' and it is not a system user');
            }
        }
    }

    public function dispatchSendPacket(Packet $packet, Session $toUserSession){

    }

    public function dispatchOfflinePacket(Session $session){

    }
}