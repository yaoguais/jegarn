<?php

namespace jegarn\listener;

use jegarn\session\Session;

interface SessionInterface {

    public function filterSession(Session $session);
    public function addSession(Session $session);
    public function removeSession(Session $session);
}