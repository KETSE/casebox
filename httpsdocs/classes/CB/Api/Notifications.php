<?php

namespace CB\Api;

class Notifications
{

    /**
     * get notifications list
     * @param  array $p params (limit)
     * @return json  response
     */
    public function getList($p)
    {
        $class = new \CB\Notifications();

        return $class->getList($p);
    }

    /**
     * get new notifications count for current user
     * @param  array $p containing fromId property
     * @return json  response
     */
    public function getNewCount($p)
    {
        $class = new \CB\Notifications();

        return $class->getNewCount($p);
    }

    /**
     * mark notification record(s) as read
     * @param  array $p containing "ids" property
     * @return json  response
     */
    public function markAsRead($p)
    {
        $class = new \CB\Notifications();

        return $class->markAsRead($p);
    }

    /**
     * mark all notifications  as read fr current use
     * @return json response
     */
    public function markAllAsRead()
    {
        $class = new \CB\Notifications();

        return $class->markAllAsRead();
    }
}
