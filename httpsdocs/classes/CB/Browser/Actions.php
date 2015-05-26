<?php
namespace CB\Browser;

use CB\L;
use CB\DB;
use CB\Util;
use CB\Solr;
use CB\Security;
use CB\Objects;

/**
 * class designed for actions like:
 *     - D&D
 *     - cut/copy and paste
 *     - create shortcut
 */
class Actions
{
    private $securitySetsFilter = '';

    /**
     * validation function for action input params
     *
     * @param  object  $p input params
     * @return boolean
     */
    private function validateParams(&$p)
    {
        if (empty($p['sourceIds']) && !empty($p['sourceData'])) {
            $p['sourceIds'] = array();
            foreach ($p['sourceData'] as $data) {
                $p['sourceIds'][] = $data['id'];
            }
        }
        if (empty($p['targetId']) && !empty($p['targetData'])) {
            $p['targetId'] = $p['targetData']['id'];
        }

        $p['sourceIds'] = array_unique(Util\toNumericArray(@$p['sourceIds']), SORT_NUMERIC);
        $p['targetId'] = intval(@$p['targetId']);

        return (!empty($p['sourceIds']) && !empty($p['targetId']));
    }

    /**
     * function for making some trivial checks over input params
     *
     * @param  object  $p input params
     * @return boolean | varchar    true on checks pass or error message
     */
    private function trivialChecks(&$p)
    {
        /* dummy check if not pasting an object over itself
            But maybe in this case we can make a copy of the object with prefix 'Copy of ...'
        */
        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE pid = $1
                AND id IN ('.implode(',', $p['sourceIds']).')',
            $p['targetId']
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            return L\get('CannotCopyObjectToItself');
        }
        $res->close();
        /* end of dummy check if not pasting an object over itself */

        /* dummy check if not copying inside a child of sourceIds */
        if (in_array($p['targetId'], $p['sourceIds'])) {
            return L\get('CannotCopyObjectInsideItself');
        }

        $res = DB\dbQuery(
            'SELECT pids
            FROM tree_info
            WHERE id = $1',
            $p['targetId']
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $pids = explode(',', $r['pids']);
            foreach ($p['sourceIds'] as $sourceId) {
                if (in_array($sourceId, $pids)) {
                    return L\get('CannotCopyObjectInsideItself');
                }
            }
        }
        $res->close();

        /* end of dummy check if not copying inside a child of sourceIds */

        return true;
    }

    /**
     * function to check if any objects name from sourceIds exists in targetId
     * @param  int | array $objectIds
     * @param  int         $targetId
     * @return boolean     | int  false if not exists or id of existent target
     */
    private function overwriteCheck($sourceIds, $targetId)
    {
        $rez = false;

        if (\CB\Config::get('allow_duplicates', false)) {
            return $rez;
        }

        $sourceIds = Util\toNumericArray($sourceIds);
        $res = DB\dbQuery(
            'SELECT t2.id
            FROM tree t1
            JOIN tree t2 ON
                t2.pid = $1 AND
                t1.name = t2.name AND
                t2.dstatus = 0
            WHERE t1.id in ('.implode(',', $sourceIds).')
                AND t1.dstatus = 0',
            $targetId
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['id'];
        }
        $res->close();

        return $rez;
    }

    /**
     * copy objects
     *
     * most complex method that requires many checks:
     * -   dummy checks for pasting object over itself, cycled pasting (pasting an object into some of its child)
     * -   if can read all object froum sourceIds
     * -   if can write to target
     * -   if target object does not contain already a child with the same name and ask for overwriting confirmation
     * -   when overwriting – follow security rules
     * @param object $p {
     *
     * }
     * @return json responce    [
     *     success
     *     pids   ids of parent objects that have changed their childs
     * ]
     */
    public function copy($p)
    {
        if (!$this->validateParams($p)) {
            return array('success' => false, 'msg' => L\get('ErroneousInputData'));
        }

        $msg = $this->trivialChecks($p);
        if ($msg !== true) {
            return array('success' => false, 'msg' => $msg);
        }

        /* security checks */
        foreach ($p['sourceIds'] as $sourceId) {
            if (!\CB\Security::canRead($sourceId)) {
                return array('success' => false, 'msg' => L\get('Access_denied'));
            }
        }
        /* there could be a situation when overwriting existing objects
            and in this case we should check for update rigths on
            those existing objects
        */
        if (!\CB\Security::canWrite($p['targetId'])) {
            return array('success' => false, 'msg' => L\get('Access_denied'));
        }
        /* end of security checks */

        if (empty($p['confirmedOverwrite'])) {
            if ($this->overwriteCheck($p['sourceIds'], $p['targetId']) !== false) {
                return array(
                    'success' => false,
                    'confirm' => true,
                    'msg' => L\get('ConfirmOverwriting')
                );
            }
        }

        $processedIds = $this->doAction('copy', $p['sourceIds'], $p['targetId']);
        $rez = array(
            'success' => !empty($processedIds)
            ,'processedIds' => $processedIds
        );
        Solr\Client::runCron();

        return $rez;
    }

    /**
     * move objects
     * @param  object $p inputParams
     * @return json   responce
     */
    public function move($p)
    {
        if (!$this->validateParams($p)) {
            return array('success' => false, 'msg' => L\get('ErroneousInputData'));
        }

        $msg = $this->trivialChecks($p);
        if ($msg !== true) {
            return array('success' => false, 'msg' => $msg);
        }

        /* security checks */
        foreach ($p['sourceIds'] as $sourceId) {
            if (!\CB\Security::canRead($sourceId)) {
                return array('success' => false, 'msg' => L\get('Access_denied'));
            }
        }
        if (!\CB\Security::canWrite($p['targetId'])) {
            return array('success' => false, 'msg' => L\get('Access_denied'));
        }
        /* end of security checks */

        if (empty($p['confirmedOverwrite'])) {
            if ($this->overwriteCheck($p['sourceIds'], $p['targetId']) !== false) {
                return array(
                    'success' => false,
                    'confirm' => true,
                    'msg' => L\get('ConfirmOverwriting')
                );
            }
        }

        $processedIds = $this->doAction('move', $p['sourceIds'], $p['targetId']);
        $rez = array(
            'success' => !empty($processedIds)
            ,'processedIds' => $processedIds
        );
        Solr\Client::runCron();

        return $rez;
    }

    /**
     * internal function executing a copy or move action
     * @param  array $sourceIds ids to be copied
     * @param  int   $targetId
     * @return array processed ids
     */
    private function doAction($action, $objectIds, $targetId)
    {
        $rez = array();
        // all the copy process will be made in a single transaction
        DB\startTransaction();

        //get security sets to which this user has
        //read access for copy or delete access for move
        $this->securitySetsFilter = '';
        if (!Security::isAdmin()) {
            $ss = array();
            switch ($action) {
                case 'copy':
                    $ss = \CB\Security::getSecuritySets();
                    break;
                case 'move':
                    //check if the user can move, because it doesnt anctually delete the obj, but just move it
                    $ss = \CB\Security::getSecuritySets(false, 5);
                    break;
            }
            $this->securitySetsFilter = 'AND ti.security_set_id in (0'.implode(',', $ss).')';
        }

        /* select only objects that current user can delete */
        $accessibleIds = array();

        $res = DB\dbQuery(
            'SELECT t.id
            FROM tree t
            JOIN tree_info ti ON
                t.id = ti.id '.$this->securitySetsFilter.'
            WHERE t.id in ('.implode(',', $objectIds).')
                AND t.dstatus = 0'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $accessibleIds[] = $r['id'];
        }
        $res->close();

        if (!empty($accessibleIds)) {
            $this->objectsClass = new \CB\Objects();
            $rez = $this->doRecursiveAction($action, $accessibleIds, $targetId);
        } else {
            throw new \Exception(L\get('Access_denied'), 1);
        }

        DB\commitTransaction();

        return $rez;
    }

    /**
     * recursive objects moving or copying
     * @param  int|array $objectIds source object ids
     * @param  int       $targetId  target id
     * @return array     processed ids
     */
    public function doRecursiveAction($action, $objectIds, $targetId)
    {
        $rez = array();
        if (!is_array($objectIds)) {
            $objectIds = Util\toNumericArray($objectIds);
        }
        if (empty($objectIds)) {
            return false;
        }

        foreach ($objectIds as $objectId) {
            $newId = null;

            // check if object with same name exist in target
            $existentTargetId = $this->overwriteCheck($objectId, $targetId);

            if ($existentTargetId == false) {
                // copy by creating a new object in target or just move
                switch ($action) {
                    case 'copy':
                        $newId = $this->objectsClass->copy($objectId, $targetId);
                        break;
                    case 'move':
                        $newId = $this->objectsClass->move($objectId, $targetId);
                        break;
                }
            } else {
                switch ($action) {
                    case 'copy':
                        $newId = $this->objectsClass->copy($objectId, $targetId, $existentTargetId);
                        break;
                    case 'move':
                        $newId = $this->objectsClass->move($objectId, $targetId, $existentTargetId);
                        break;
                }
            }
            // skip childs copy if object not copied/moved
            if (empty($newId)) {
                continue;
            }
            $rez[] = $newId;
            // skip childs moving if moved object is itself
            if ($newId == $objectId) {
                continue;
            }

            // select direct childs of the objects and make a recursive call with them
            $res = DB\dbQuery(
                'SELECT t.id
                FROM tree t
                JOIN tree_info ti ON
                    t.id = ti.id '.$this->securitySetsFilter.'
                WHERE t.pid = $1 AND t.dstatus = 0',
                $objectId
            ) or die(DB\dbQueryError());

            $childIds = array();
            while ($r = $res->fetch_assoc()) {
                $childIds[] = $r['id'];
            }
            $res->close();
            $this->doRecursiveAction($action, $childIds, $newId);
        }

        return $rez;
    }

    /**
     * create shorcut(s)
     * @param  object $p input params
     * @return json   responce
     */
    public function shortcut($p)
    {
        if (!$this->validateParams($p)) {
            return array('success' => false, 'msg' => L\get('ErroneousInputData'));
        }
        /* security checks */
        foreach ($p['sourceIds'] as $sourceId) {
            if (!\CB\Security::canRead($sourceId)) {
                return array('success' => false, 'msg' => L\get('Access_denied'));
            }
        }
        if (!\CB\Security::canWrite($p['targetId'])) {
            return array('success' => false, 'msg' => L\get('Access_denied'));
        }

        $rez = array(
            'success' => true
            ,'targetId' => $p['targetId']
            ,'processedIds' => array()
        );

        $shortcutObject = new Objects\Shortcut();

        foreach ($p['sourceIds'] as $id) {
            $rez['processedIds'][] = $shortcutObject->create(
                array(
                    'id' => null
                    ,'pid' => $p['targetId']
                    ,'target_id' => $id
                )
            );
        }

        Solr\Client::runCron();

        return $rez;
    }
}
