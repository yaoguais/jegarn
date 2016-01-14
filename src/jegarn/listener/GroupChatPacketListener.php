<?php

namespace jegarn\listener;

use jegarn\manager\GroupManager;
use jegarn\packet\Packet;
use jegarn\session\Session;
use jegarn\manager\OfflineGroupMessageManager;
use jegarn\manager\ServerManager;
use jegarn\manager\SessionManager;
use jegarn\packet\GroupChatPacket;
use jegarn\log\Logger;


class GroupChatPacketListener implements PacketInterface{

    public function dispatchReceivePacket(Packet $packet, Session $session){
        if(!$session->isAuth()) return;
        /* @var \jegarn\session\SwooleSession $session
         * @var GroupChatPacket $pkt
         */
        if($pkt = GroupChatPacket::getPacketFromPacket($packet)){
            // check send user is a member of this group
            $gm = GroupManager::getInstance();
            $fromUid = $pkt->getFrom();
            $isSystemUser = $packet->isFromSystemUser();
            if($isSystemUser || $gm->isGroupUser($pkt->getGroupId(), $fromUid)){
                if($pkt->isSendToAll()){
                    if($uidList = $gm->getGroupUsers($pkt->getGroupId())){
                        foreach($uidList as $uid){
                            if($uid != $fromUid){
                                $this->sendOrSavePacket($pkt, $uid);
                            }
                        }
                    }else{
                        Logger::addInfo('group ' . $pkt->getGroupId() . ' users lost, from uid ' . $fromUid);
                    }
                }else{
                    $toUid = $pkt->getTo();
                    if($fromUid != $toUid){
                        if($isSystemUser || $gm->isGroupUser($pkt->getGroupId(), $toUid)){
                            $this->sendOrSavePacket($pkt, $toUid);
                        }else{
                            Logger::addInfo('group ' . $pkt->getGroupId() . ' users lost, from uid ' . $fromUid);
                        }
                    }
                }
            }else{
                Logger::addInfo('group ' . $pkt->getGroupId() . ' has no user ' . $fromUid);
            }
        }
    }

    public function dispatchSendPacket(Packet $packet, Session $toUserSession){
        /* @var \jegarn\session\SwooleSession $toSession
         * @var GroupChatPacket $pkt
         */
        if($pkt = GroupChatPacket::getPacketFromPacket($packet)){
            if(!$toUserSession->isReachable() || !$toUserSession->isAuth()){
                OfflineGroupMessageManager::getInstance()->addPacket($packet->getTo(), $pkt);
            }
        }
    }

    public function dispatchOfflinePacket(Session $session){
        // if(!$session->isAuth() || !$session->getUserId()) return; // before dispatch, both two is checked
        $ogm = OfflineGroupMessageManager::getInstance();
        $uid = $session->getUserId();
        $sm = ServerManager::getInstance();
        while($packet = $ogm->getPacket($uid)){
            $sm->send($session, $packet);
        }
    }

    protected function sendOrSavePacket(GroupChatPacket $packet, $uid){
        $pkt = clone $packet;
        $pkt->setTo($uid);
        if($toSession = SessionManager::getInstance()->getSessionByUserId($uid)){
            Logger::addInfo('user online,groupchat(uid:' . $uid . ')');
            ServerManager::getInstance()->send($toSession, $pkt);
        }else{
            Logger::addInfo('user not online,groupchat,storage message(uid:' . $uid . ')');
            OfflineGroupMessageManager::getInstance()->addPacket($uid, $pkt);
        }
    }
}