<?php
namespace Notifications;

class Listeners
{
    /**
     * create notification when adding actions to log
     * @param  array $p params passed to log
     * @return void
     */
    public function onLogAdd(&$p)
    {
        $o = empty($p['new'])
            ? ( empty($p['old'])
                ? $p['data']
                : $p['old']
            )
            : $p['new'];

        // for now we add notifications only for tree items
        if (!is_object($o)) {
            return;
        }

        switch ($o->getType()) {
            case 'login':
            case 'logout':
            case 'login_fail':

                break;
            case 'task':
            case 'event':
                Tasks::addNotifications($p);
                break;

            case 'comment':
                Comments::addNotifications($p);
                break;

            default:
                Objects::addNotifications($p);
        }
    }
}
