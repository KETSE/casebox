<?php
namespace SystemFolders;

use CB\DB;
use \CB\Browser;
use CB\Util;

class Listeners
{
    /**
     * create system folders specified in created objects template config as system_folders property
     * @param  object $o
     * @return void
     */
    public function onNodeDbCreate($o)
    {
        if (!is_object($o)) {
            return;
        }
        $template = $o->getTemplate();
        if (empty($template)) {
            return;
        }

        $templateData = $template->getData();
        if (empty($templateData['cfg']['system_folders'])) {
            return;
        }

        $folderIds = Util\toNumericArray($templateData['cfg']['system_folders']);

        if (empty($folderIds)) {
            return;
        }

        $p = array(
            'sourceIds' => array()
            ,'targetId' => $o->getData()['id']
        );

        $browserActionsClass = new Browser\Actions();
        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE pid in ('.implode(',', $folderIds).')'
        ) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $p['sourceIds'][] = $r['id'];
        }
        $res->close();

        // $browserActionsClass->copy($p);

        $browserActionsClass->objectsClass = new \CB\Objects();

        $browserActionsClass->doRecursiveAction(
            'copy',
            $p['sourceIds'],
            $p['targetId']
        );
    }
}
