<?php

namespace CB;

use CB\DataModel as DM;

class Notifications
{
    private static $template = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" lang="{lang}" xml:lang="{lang}">
        <head><title>CaseBox</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head>
        <body>{body}</body></html>';

    private static $updateFieldColors = array(
        'added' => 'A8D08D'
        ,'updated' => '#FFC000'
        ,'removed' => 'C00000'
    );

    /**
     * remotely accessible method to get the notifications list
     * @param  arra $p remote params (page, limit, etc)
     * @return json response
     */
    public function getList($p)
    {
        $this->prepareParams($p);

        $params = array(
            'user_id' => User::getId()
            ,'limit' => $p['limit']
        );

        return array(
            'success' => true
            ,'data' => $this->getRecords($params)
        );
    }

    /**
     * get new notifications count
     * @param  array $p containing fromId property
     * @return json  response
     */
    public function getNewCount($p)
    {
        $rez = array(
            'success' => true
            ,'count' => 0
        );

        $this->prepareParams($p);

        $fromId = empty($p['fromId'])
            ? false
            : intval($p['fromId']);

        $rez['count'] = DM\Notifications::getCount(
            User::getId(),
            $fromId
        );

        return $rez;
    }

    /**
     * mark a notification record as read
     * @param  array $p containing "id" (returned client side id) and "ids"
     * @return json  response
     */
    public function markAsRead($p)
    {
        $rez = array('success' => false);

        if (empty($p['ids'])) {
            return $rez;
        }

        DM\Notifications::markAsRead(User::getId(), $p['ids']);

        return array(
            'success' => true
            ,'data' => $p
        );
    }

    /**
     * mark all unread user notifications  as read
     * @return json response
     */
    public function markAllAsRead()
    {
        DM\Notifications::markAllAsRead(User::getId());

        return array(
            'success' => true
        );
    }

    /**
     * get action records and group them for notifications display
     * @param  varchar $sql
     * @param  array   $params sql params
     * @return array
     */
    private function getRecords($p)
    {
        $rez = array();

        $recs = DM\Notifications::getLast(
            $p['user_id'],
            $p['limit'],
            empty($p['fromId']) ? false : $p['fromId']
        );

        $actions = array();
        //grouping actions by object_id, action type and read property
        foreach ($recs as $r) {
            $r['data'] = Util\toJsonArray($r['data']);
            $group = $r['object_id'] . '_' . $r['action_type'] . '_' . $r['read'];
            $actions[$group][$r['user_id']] = $r;
        }

        //iterate actions and group into records up to read property
        foreach ($actions as $group => $users) {
            //form id
            $ids = array(); //would be comma separated action_ids
            foreach ($users as $r) {
                $ids[] = $r['id'];
            }
            $r = current($users);

            $record = array(
                'ids' => implode(',', $ids)
                ,'read' => $r['read']
                ,'user_id' => $r['user_id']
                ,'object_id' => $r['object_id']
                ,'text' => $this->getUsersString($users) . ' ' .
                        $this->getActionDeclination($r['action_type']) . ' ' .
                        $this->getObjectName($r['data'])  . //with icon
                        '<div class="cG">' . Util\formatAgoTime($r['action_time']). '</div>'

            );

            $rez[] = $record;
        }

        return $rez;
    }

    /**
     * forms a user string based on their count
     * @param  array   &$usersArray grouped users array
     * @return varchar
     */
    private function getRecordIconClass(&$usersArray)
    {
        $userId = key($usersArray);

        $rez = '<img class="photo32" src="photo/' . $userId . '.jpg?32=' . User::getPhotoParam($userId) .
                '" style="width:32px; height: 32px">';
    }

    /**
     * forms a user string based on their count
     * @param  array   &$usersArray grouped users array
     * @return varchar
     */
    private function getUsersString(&$usersArray)
    {
        $rez = '';

        $usersCount = sizeof($usersArray);
        $userIds = array_keys($usersArray);

        switch ($usersCount) {
            case 0:
                break;

            case 1:
                $rez = '<a>' . User::getDisplayName($userIds[0]) . '</a>';
                break;

            case 2:
                $rez = '<a>' . User::getDisplayName($userIds[0]) .  '</a> ' .
                    L\get('and') .
                    ' <a>' . User::getDisplayName($userIds[1]) . '</a>';
                break;

            case 3:
                $rez = '<a>' . User::getDisplayName($userIds[0]) .  '</a>' .
                    ', ' .
                    '<a>' . User::getDisplayName($userIds[1]) . '</a> ' .
                    L\get('and') .
                    ' <a>' . User::getDisplayName($userIds[1]) . '</a>';
                break;

            default:
                $rez = '<a>' . User::getDisplayName($userIds[0])  . '</a>' .
                    ', ' .
                    '<a>' . User::getDisplayName($userIds[1])  . '</a> ' .
                    L\get('and') . ' ' .
                    str_replace('{count}', $usersCount -2, L\get('NNOthers'));
        }

        return $rez;
    }

    /**
     * get action type declination
     * @param  varchar $actionType
     * @param  varchar $lang
     * @return varchar
     */
    public static function getActionDeclination($actionType, $lang = false)
    {
        $rez = '';
        switch ($actionType) {
            case 'create':
            case 'update':
            case 'delete':
            case 'complete':
            case 'close':
            case 'rename':
            case 'status_change':
            case 'comment_update':
            case 'move':
                $rez = L\get($actionType . 'd', $lang);
                break;

            case 'reopen':
                $rez = L\get($actionType . 'ed', $lang);
                break;

            case 'comment':
                $rez = L\get($actionType . 'edOn', $lang);
                break;

            default:
                $rez = $actionType;
                //to review and discuss
                /*'completion_decline'
                'completion_on_behalf'
                'overdue'
                'password_change'
                'permissions'
                'user_delete'
                'user_create'
                'login'
                'login_fail'/**/
        }

        return $rez;
    }

    /**
     * format an object name using its data
     * @param  array   $data
     * @return varchar
     */
    private function getObjectName($data)
    {
        return '<a class="click obj-ref" itemid="'. $data['id'] .
            '" templateid="'. $data['template_id'] .
            '" title="'. $data['name'] .
            '">'. $data['name'] . '</a>';
    }

    /**
     * prepare input params of a request
     * @param  array &$p
     * @return void
     */
    protected function prepareParams(&$p)
    {
        $limit = (empty($p['limit']) || !is_numeric($p['limit']))
            ? 200
            : intval($p['limit']);

        if ($limit > 500) {
            $limit = 500;
        }

        $p['limit'] = $limit;
    }

    //-------------------  rendering methods

    /**
     * analize given action (from action log)
     * and create corresponding mail body
     * @return varchar
     */
    public static function getMailBodyForAction(&$action, &$userData)
    {
        $coreUrl = Config::get('core_url');
        $name = $action['data']['name'];
        $languages = Config::get('languages');
        $lang = $languages[$userData['language_id'] -1];

        //set header row by default
        $rez = '<h3><a href="' . $coreUrl . 'view/' . $action['object_id'] . '/">' . $name . '</a></h3>';

        switch ($action['action_type']) {
            case 'comment':
            case 'comment_update':
                $rez .= static::getCommentMailBody($action, $userData);
                break;

            case 'create':
            case 'delete':
            case 'move':
                $rez .= static::getObjectMailBody($action);
                break;

            default:
                $rez .= static::getActionDiffMailBody($action, static::$updateFieldColors);
        }

        $rez = str_replace(
            array(
                '{lang}',
                '{body}'
            ),
            array(
                $lang,
                $rez
            ),
            static::$template
        );

        return $rez;
    }

    /**
     * get mail body for a comment
     * @param  array   $action
     * @param  array   $colors
     * @return varchar
     */

    protected static function getCommentMailBody(&$action, &$userData)
    {
        $rez = nl2br(\CB\Objects\Comment::processAndFormatMessage($action['data']['comment']));

        $rez .=
            '<br /><hr />'.
            'To add a comment, reply to this email.<br />';
            // <a href="#">Unsubscribe</a> (will not receive emails with new comments for “' . $name . '”)';
        return $rez;
    }

    /**
     * get mail body for a generic object
     * @param  array   $action
     * @param  array   $colors
     * @return varchar
     */
    protected static function getObjectMailBody($action, $colors = false)
    {
        $rez = '';
        $rows = array();

        $obj = Objects::getCachedObject($action['object_id']);
        $tpl = $obj->getTemplate();
        $ld = $obj->getLinearData(true);

        $ad = &$action['data'];

        foreach ($ld as $fieldData) {
            $fieldName = $fieldData['name'];
            $field = $tpl->getField($fieldName);

            $type = 'none';
            if (!empty($ad['new'][$fieldName]) && empty($ad['old'][$fieldName])) {
                $type = 'added';
            } elseif (!empty($ad['old'][$fieldName]) && empty($ad['new'][$fieldName])) {
                $type = 'removed';
            } elseif (!empty($ad['old'][$fieldName]) && !empty($ad['new'][$fieldName]) &&
                ($ad['old'][$fieldName] != $ad['new'][$fieldName])
            ) {
                $type = 'updated';
            }

            $color = empty($colors[$type])
                ? ''
                : (' style="background-color: ' . $colors[$type] . '"');

            $value = $tpl->formatValueForDisplay($field, $fieldData, true);
            if (!empty($value) || ($type == 'removed')) {
                $rows[] = '<tr><td style="vertical-align: top"><strong>' . $field['title'] . '</strong></td>' .
                    '<td' . $color . '>' . $value . '</td></tr>';
            }
        }

        if (!empty($rows)) {
            $rez = '<table border="1" style="border-collapse: collapse" cellpadding="3">'.
                '<th style="background-color: #d9d9d9">' . L\get('Property') . '</th>' .
                '<th style="background-color: #d9d9d9">' . L\get('Value') . '</th></tr>' .
                implode("\n", $rows) . '</table>';
        }

        return $rez;
    }

    /**
     * get mail body diff for a generic object
     * @param  array   $action
     * @param  array   $colors
     * @return varchar
     */
    protected static function getActionDiffMailBody($action, $colors = false)
    {
        $rez = '';
        $rows = array();

        $obj = Objects::getCachedObject($action['object_id']);
        $tpl = $obj->getTemplate();

        $ad = &$action['data'];

        $oldData = empty($ad['old'])
            ? array()
            : $ad['old'];

        $newData = empty($ad['new'])
            ? array()
            : $ad['new'];

        $keys = array_keys($oldData + $newData);

        foreach ($keys as $fieldName) {
            $field = $tpl->getField($fieldName);

            $oldValue = null;
            if (!empty($oldData[$fieldName])) {
                $oldValue = array();
                foreach ($oldData[$fieldName] as $v) {
                    $v = $tpl->formatValueForDisplay($field, $v, true);
                    if (!empty($v)) {
                        $oldValue[] = $v;
                    }
                }
                $oldValue = implode('<br />', $oldValue);
            }

            $newValue = null;
            if (!empty($newData[$fieldName])) {
                $newValue = array();
                foreach ($newData[$fieldName] as $v) {
                    $v = $tpl->formatValueForDisplay($field, $v, true);
                    if (!empty($v)) {
                        $newValue[] = $v;
                    }
                }
                $newValue = implode('<br />', $newValue);
            }

            $type = 'none';
            if (!empty($newValue) && empty($oldValue)) {
                $type = 'added';
            } elseif (!empty($oldValue) && empty($newValue)) {
                $type = 'removed';
            } elseif (!empty($oldValue) && !empty($newValue) && ($oldValue != $newValue)) {
                $type = 'updated';
            }

            $color = empty($colors[$type])
                ? ''
                : (' style="background-color: ' . $colors[$type] . '"');

            $value = empty($oldValue)
                ? ''
                : "<del>$oldValue</del><br />";
            $value .= $newValue;

            if (!empty($value)) {
                $rows[] = '<tr><td style="vertical-align: top"><strong>' . $field['title'] . '</strong></td>' .
                    '<td' . $color . '>' . $value . '</td></tr>';
            }
        }

        if (!empty($rows)) {
            $rez = '<table border="1" style="border-collapse: collapse" cellpadding="3">'.
                '<th style="background-color: #d9d9d9">' . L\get('Property') . '</th>' .
                '<th style="background-color: #d9d9d9">' . L\get('Value') . '</th></tr>' .
                implode("\n", $rows) . '</table>';
        }

        return $rez;
    }

    /**
     * get the sender formated string
     * @param  int     $userId
     * @return varchar
     */
    public static function getSender($userId = false)
    {
        $coreName = Config::get('core_name');

        $commentsEmail = Config::get('comments_email');

        $senderMail = empty($commentsEmail)
            ? Config::get('sender_email')
            : $commentsEmail;

        $rez = '"' .
            mb_encode_mimeheader(
                str_replace(
                    '"',
                    '\'\'',
                    html_entity_decode(
                        User::getDisplayName($userId) . " (" . $coreName . ")",
                        ENT_QUOTES,
                        'UTF-8'
                    )
                ),
                'UTF-8',
                'B'
            )
            ."\" <" . $senderMail . '>';

        return $rez;
    }
}
