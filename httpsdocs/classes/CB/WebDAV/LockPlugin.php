<?php
namespace CB\WebDAV;
use \Sabre\DAV\Server;

class LockPlugin extends \Sabre\DAV\ServerPlugin {
    protected $server;

    function getName() {
        return 'cblock';
    }

    function initialize(Server $server){

        $this->server = $server;
        $server->on('beforeLock', [$this, 'beforeLock']);
    }

    function beforeLock($path, \Sabre\DAV\Locks\LockInfo $lock) {
        $lock->owner = \CB\User::getDisplayName($_SESSION['user']['id']);
        // error_log('beforeLock: ' . $lock->owner);
        return true;
    }
}