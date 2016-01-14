<?php

namespace jegarn\listener;

use jegarn\packet\Packet;
use jegarn\session\Session;

interface PacketInterface {

    public function dispatchReceivePacket(Packet $packet, Session $session);
    public function dispatchSendPacket(Packet $packet, Session $toUserSession);
    public function dispatchOfflinePacket(Session $session);
}