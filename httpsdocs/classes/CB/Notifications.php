<?php

namespace CB;

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
            $rez = $action['action_type'].'<table border="1" style="border-collapse: collapse" cellpadding="3">'.
                '<th style="background-color: #d9d9d9">' . L\get('Property') . '</th>' .
                '<th style="background-color: #d9d9d9">' . L\get('Value') . '</th></tr>' .
                implode("\n", $rows) . '</table>';
        }

        return $rez;
    }

    /**
     * get the sender formated string
     * @return varchar
     */
    public static function getSender()
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
                        User::getDisplayName() . " (" . $coreName . ")",
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
