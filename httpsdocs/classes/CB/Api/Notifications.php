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
     * get new notification records
     * @param  array $p containing fromId property
     * @return json  response
     */
    public function getNew($p)
    {
        $class = new \CB\Notifications();

        return $class->getNew($p);
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
