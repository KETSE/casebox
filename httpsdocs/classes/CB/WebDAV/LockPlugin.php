<?php
namespace CB\WebDAV;
use \Sabre\DAV\Server;

class LockPlugin extends \Sabre\DAV\ServerPlugin
{
    protected $server;

    public function getName()
    {
        return 'cblock';
    }

    public function initialize(Server $server)
    {
        $this->server = $server;
        $server->on('beforeLock', [$this, 'beforeLock']);
    }

    public function beforeLock($path, \Sabre\DAV\Locks\LockInfo $lock)
    {
        $path = $path; //dummy codacy assignment
        $lock->owner = \CB\User::getDisplayName($_SESSION['user']['id']);
        // error_log('beforeLock: ' . $lock->owner);
        return true;
    }
}
