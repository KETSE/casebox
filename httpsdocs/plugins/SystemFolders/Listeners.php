<?php
namespace SystemFolders;

use CB\DataModel as DM;
use CB\DB;
use CB\Util;
use CB\User;

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

        $ownerId = User::getId();
        $pid = $o->getData()['id'];
        $copyIds = [];

        $res = DB\dbQuery('SELECT id FROM tree WHERE pid in (' . implode(',', $folderIds). ') AND dstatus = 0');
        while ($r = $res->fetch_assoc()) {
            $copyIds[] = ['id' => $r['id'], 'pid' => $pid];
        }
        $res->close();

        while (!empty($copyIds)) {
            $r = array_shift($copyIds);
            $newId = DM\Tree::copy($r['id'], $r['pid'], $ownerId);
            DM\Objects::copy($r['id'], $newId);

            //collect children of copied element and add them to the end
            $res = DB\dbQuery('SELECT id FROM tree WHERE pid = $1 AND dstatus = 0', $r['id']);
            while ($r = $res->fetch_assoc()) {
                $copyIds[] = ['id' => $r['id'], 'pid' => $newId];
            }
            $res->close();
        }
    }
}
