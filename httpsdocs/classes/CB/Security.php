<?php
namespace CB;

/**
 * Security class
 *
 * This class works user acceses that are divided into bits.
 * There are following user access bits defined, wich are defined as static constants of this class:
 *       0 List Folder/Read Data
 *       1 Create Folders
 *       2 Create Files
 *       3 Create Actions
 *       4 Create Tasks
 *       5 Read
 *       6 Write
 *       7 Delete child nodes
 *       8 Delete
 *       9 Change permissions
 *       10 Take Ownership
 *       11 Download
 */

use CB\Cache;
use CB\DataModel as DM;

class Security
{
    /* static constants */
    public static $CAN_LIST_FOLDERS = 0;
    public static $CAN_CREATE_FOLDERS = 1;
    public static $CAN_CREATE_FILES = 2;
    public static $CAN_CREATE_ACTIONS = 3;
    public static $CAN_CREATE_TASKS = 4;
    public static $CAN_READ = 5;
    public static $CAN_WRITE = 6;
    public static $CAN_DELETE_CHILDS = 7;
    public static $CAN_DELETE = 8;
    public static $CAN_CHANGE_PERMS = 9;
    public static $CAN_TAKE_OWNERSHIP = 10;
    public static $CAN_DOWNLOAD = 11;
    /* groups methods */

    /**
     * Retreive defined groups
     * Used in CB.DB for groupsStore
     * @returns array of groups records
     */
    public static function getUserGroups()
    {
        $rez = array(
            'success' => true,
            'data' => DM\UsersGroups::getAvailableGroups()
        );

        return $rez;
    }

    /**
     * Create a security group
     *
     * @returns group properties
     */
    public function createUserGroup($p)
    {
        $p['success'] = true;

        if (!Security::canAddGroup()) {
            throw new \Exception(L\get('Access_denied'));
        }

        $p['data']['name'] = trim(strip_tags($p['data']['name']));

        // check if group with that name already exists
        $id = DM\Group::getIdByName($p['data']['name']);
        if (!empty($id)) {
            trigger_error(L\get('Group_exists') . ' "' . $p['data']['name'] . '"', E_USER_ERROR);
        }

        //create group
        $name = $p['data']['name'];
        $p['data']['id'] = DM\Group::create(
            array(
                'name' => $name
                // ,'l1' => $name
                // ,'l2' => $name
                // ,'l3' => $name
                // ,'l4' => $name
                ,'cid' => User::getId()
            )
        );

        return $p;
    }

    /**
     * Update group name (placeholder)
     */
    public function updateUserGroup($p)
    {
        if (!Security::isAdmin()) {
            throw new \Exception(L\get('Access_denied'));
        }

        return array( 'success' => true, 'data' => array());
    }

    /**
     * Delete a security group
     */
    public function destroyUserGroup($p)
    {
        if (!Security::isAdmin()) {
            throw new \Exception(L\get('Access_denied'));
        }

        DM\Group::delete($p);

        return array(
            'success' => true
            ,'data' => $p
        );
    }
    /* end of groups methods */

    /**
     * Search users and/or groups
     *
     * Used from fields of type "objects" with source "users"/"groups"/"usergroups"
     *
     * This function receives field config as parameter (inluding text query) and returns the matched results.
     */
    public function searchUserGroups($p)
    {
        /*{"editor":"form","source":"users","renderer":"listObjIcons",
        "autoLoad":true,"multiValued":true,"maxInstances":1,
        "showIn":"grid","query":"test","objectId":"237","path":"/1"}*/
        $rez = array(
            'success' => true
            ,'data' => array()
        );

        $where = array();
        $params = array();

        if (!empty($p['source'])) {
            switch ($p['source']) {
                case 'users':
                    $where[] = '`type` = 2';
                    break;
                case 'groups':
                    $where[] = '`type` = 1';
                    break;
            }
        } elseif (!empty($p['types'])) {
            $a = Util\toNumericArray($p['types']);
            if (!empty($a)) {
                $where[] = '`type` in ('.implode(',', $a).')';
            }
        }

        $filterDisabled = '(enabled = 1)';

        if (!empty($p['query'])) {
            $where[] = 'searchField like $1';
            $params[] = ' %'.trim($p['query']).'% ';

        } elseif (!empty($p['value'])) {
            //if the id is in value we should show it
            //even if disabled
            $ids = Util\toNumericArray($p['value']);
            if (!empty($ids)) {
                $filterDisabled = '((' . $filterDisabled . ') OR id in (' . implode(',', $ids). '))';
            }

        }

        if (!empty($p['ids'])) {
            $ids = Util\toNumericArray($p['ids']);
            if (!empty($ids)) {
                $where[] = 'id in (' . implode(',', $ids) . ')';
            }
        }

        $res = DB\dbQuery(
            'SELECT id
                ,`name`
                ,`first_name`
                ,`last_name`
                ,`email`
                ,`system`
                ,`enabled`
                ,`type`
                ,`sex`
            FROM users_groups
            WHERE did IS NULL AND ' . $filterDisabled .
            (empty($where) ? '' : ' AND '.implode(' AND ', $where)) .
            ' ORDER BY `type`, 2 LIMIT 100',
            $params
        );

        while ($r = $res->fetch_assoc()) {
            if ($r['type'] == 1) {
                $r['iconCls'] = 'icon-users';

            } else {
                $r['user'] = $r['name'];
                $r['name'] = User::getDisplayName($r);
                $r['iconCls'] = 'icon-user-'.$r['sex'];
            }

            unset($r['first_name']);
            unset($r['last_name']);
            unset($r['type']);
            unset($r['sex']);

            $rez['data'][] = $r;
        }
        $res->close();

        return $rez;
    }

    /**
     * get objects acl list
     * @param  $p       client side request params with field config
     * @param  boolean $inherited flag to include inherited rules also
     * @return array   json responce
     */
    public function getObjectAcl($p, $inherited = true)
    {
        $rez = array(
            'success' => true
            ,'data' => array()
            ,'name' => ''
        );

        if (!is_numeric($p['id'])) {
            return $rez;
        }

        $id = $p['id'];

        if (empty($this->internalAccessing)
            && !Security::canRead($id)
        ) {
            throw new \Exception(L\get('Access_denied'));
        }

        $obj = Objects::getCachedObject($id);
        $od = $obj->getData();

        $rez['name'] = $obj->getHtmlSafeName();
        $rez['inherit_acl'] = $od['inherit_acl'];

        /* set object title, path and inheriting access ids path*/
        $objIds = array();
        $res = DB\dbQuery(
            'SELECT
                ti.`path`
                ,ts.`set` `obj_ids`
            FROM tree_info ti
            LEFT JOIN tree_acl_security_sets ts ON ti.security_set_id = ts.id
            WHERE ti.id = $1',
            $id
        );

        if ($r = $res->fetch_assoc()) {
            $objIds = explode(',', $r['obj_ids']);
        }
        $res->close();
        /* end of set object title and path*/

        /* get the full set of access credentials(users and/or groups) including inherited from parents */
        $res = DB\dbQuery(
            'SELECT DISTINCT u.id
                    ,u.`name`
                    ,u.`first_name`
                    ,u.`last_name`
                    ,u.`system`
                    ,u.`enabled`
                    ,u.`type`
                    ,u.`sex`
                FROM tree_acl a
                JOIN users_groups u ON a.user_group_id = u.id
                WHERE a.node_id '.(
                    $inherited
                    ? ' in (0'.implode(',', $objIds).')'
                    : ' = $1 '
                ).' ORDER BY u.`type`, 2',
            $id
        );

        while ($r = $res->fetch_assoc()) {
            $r['user_group_id'] = $r['id'];
            $r['name'] = User::getDisplayName($r);
            $r['iconCls'] = ($r['type'] == 1) ? 'icon-users' : 'icon-user-'.$r['sex'];

            unset($r['sex']);
            $access = $this->getUserGroupAccessForObject($id, $r['id']);
            $r['allow'] = implode(',', $access[0]);
            $r['deny'] = implode(',', $access[1]);
            $rez['data'][] = $r;
        }
        $res->close();
        /* end of get the full set of access credentials(users and/or groups) including inherited from parents */

        return $rez;
    }

    /**
    * Returns estimated bidimentional array of access bits, from object acl, for a user or group
    *
    * Used for access display in interface
    * Returned array has to array elements:
    *   first - array bits for allow access
    *   second - array bits for deny access
    * Each bit can have the following values:
    *   -2 - deny, inherited from a parent
    *   -1 - deny, directly set for the object
    *    0 - not set
    *    1 - allow, directly set for the object
    *    2 - allow, inherited from a parent
    *
    *   Permission Precedence:
    *       Explicit Deny (access set for input object_id, not estimated in summary with near accesses for input object_id)
    *       Explicit Allow (access set for input object_id, not estimated in summary with near accesses for input object_id)
    *       Inherited Deny (access inherited from all parents)
    *       Inherited allow (access inherited from all parents)
    */
    private static function getUserGroupAccessForObject($object_id, $user_group_id = false)
    {
        /* if no user is specified as parameter then calculating for current loged user */

        if ($user_group_id === false) {
            $user_group_id = User::getId();
        }

        /* prepearing result array (filling it with zeroes)*/
        $rez = array(array_fill(0, 12, 0), array_fill(0, 12, 0));

        $user_group_ids = array($user_group_id);
        $everyoneGroupId = static::getSystemGroupId('everyone');
        if ($user_group_id !== $everyoneGroupId) {
            $user_group_ids[] = $everyoneGroupId;
        }

        /* getting object ids that have inherit set to true */
        $ids = array();
        $res = DB\dbQuery(
            'SELECT ts.set `ids`
            FROM tree_info ti
            JOIN tree_acl_security_sets ts ON ti.security_set_id = ts.id
            WHERE ti.id = $1',
            $object_id
        );

        if ($r = $res->fetch_assoc()) {
            $ids = explode(',', $r['ids']);
        }
        $res->close();

        /* reversing array for iterations from object to top parent */
        $ids = array_reverse($ids);

        /* getting group ids where passed $user_group_id is a member*/
        $user_group_ids = array_merge(
            $user_group_ids,
            DM\UsersGroups::getMemberGroupIds($user_group_id)
        );
        $user_group_ids = array_unique($user_group_ids);
        $user_group_ids = Util\toNumericArray($user_group_ids);

        /* end of getting group ids where passed $user_group_id is a member*/

        $acl_order = array_flip($ids);
        $acl = array();

        // selecting access list set for our path ids
        $res = DB\dbQuery(
            'SELECT
                node_id
                ,user_group_id
                ,allow
                ,deny
            FROM tree_acl
            WHERE node_id IN (0'.implode(',', $ids).')
                AND user_group_id IN ('.implode(',', $user_group_ids).')'
        );

        while ($r = $res->fetch_assoc()) {
            $acl[$acl_order[$r['node_id']]][$r['user_group_id']] = array($r['allow'], $r['deny']);
        }
        $res->close();
        /* now iterating the $acl table and determine final set of bits/**/
        $set_bits = 0;
        $i=0;
        ksort($acl, SORT_NUMERIC);
        reset($acl);
        while (( current($acl) !== false ) && ($set_bits < 12)) {
            $i = key($acl);
            $inherited = ($i > 0) || (!isset($acl_order[$object_id]));
            $allowDirectAccess = array_fill(0, 12, 0);
            /* check firstly if direct access is specified for passed user_group_id */
            if (!empty($acl[$i][$user_group_id])) {
                $deny = intval($acl[$i][$user_group_id][1]);
                for ($j=0; $j < sizeof($rez[1]); $j++) {
                    if (empty($rez[0][$j]) && empty($rez[1][$j]) && ($deny & 1)) {
                        $rez[1][$j] = -(1 + $inherited);
                        $set_bits++;
                    }
                    $deny = $deny >> 1;
                }
                $allow = intval($acl[$i][$user_group_id][0]);
                for ($j=0; $j < sizeof($rez[0]); $j++) {
                    if (empty($rez[0][$j]) && empty($rez[1][$j]) && ($allow & 1)) {
                        $rez[0][$j] = (1 + $inherited);
                        $allowDirectAccess[$j] = (1 + $inherited);
                        $set_bits++;
                    }
                    $allow = $allow >> 1;
                }
            }

            /* if we have direct access specified to requested user_group
            for input object_id then return just this direct access
            and exclude any other access at the same level (for our object_id) */
            if (isset($acl_order[$object_id]) && ($acl_order[$object_id]== $i)) {
                next($acl);
                continue;
            }

            if (!empty($acl[$i])) {
                foreach ($acl[$i] as $key => $value) {
                    if (($key == $user_group_id) || ($key == $everyoneGroupId)) {
                        //skip direct access setting because analized above and everyone group id will be analized last
                        continue;
                    }
                    $deny = intval($value[1]);

                    for ($j=0; $j < sizeof($rez[1]); $j++) {
                        if (empty($rez[0][$j])
                            && empty($rez[1][$j])
                            && ($deny & 1)
                            && empty($allowDirectAccess[$j])) {

                            //set deny access only if not set directly for that credential allow access
                            $rez[1][$j] = -(1 + $inherited);
                            $set_bits++;
                        }
                        $deny = $deny >> 1;
                    }
                    $allow = intval($value[0]);
                    for ($j=0; $j < sizeof($rez[0]); $j++) {
                        if (empty($rez[0][$j]) && empty($rez[1][$j]) && ($allow & 1)) {
                            $rez[0][$j] = (1 + $inherited);
                            $set_bits++;
                        }
                        $allow = $allow >> 1;
                    }
                }
            }

            // now analize for everyone group id if set, but only for higher levels (inherited parents)
            if (!empty($acl[$i][$everyoneGroupId])) {
                $value = $acl[$i][$everyoneGroupId];
                $deny = intval($value[1]);
                for ($j=0; $j < sizeof($rez[1]); $j++) {
                    if (empty($rez[0][$j])
                        && empty($rez[1][$j])
                        && ($deny & 1)
                        && empty($allowDirectAccess[$j])) {

                        //set deny access only if not set directly for that credential allow access
                        $rez[1][$j] = -(1 + $inherited);
                        $set_bits++;
                    }
                    $deny = $deny >> 1;
                }
                $allow = intval($value[0]);
                for ($j=0; $j < sizeof($rez[0]); $j++) {
                    if (empty($rez[0][$j]) && empty($rez[1][$j]) && ($allow & 1)) {
                        $rez[0][$j] = (1 + $inherited);
                        $set_bits++;
                    }
                    $allow = $allow >> 1;
                }
            }

            next($acl);
        }

        return $rez;
    }

    /**
     * estimate user access for an object
     * @param  int   $object_id
     * @param  int   $user_id   if not specified - estimating for current loged in user
     * @return array bidimentional array of bit accesses with 2 levels
     *               first level (index 0) - allow access bits
     *               second level (index 1) - deny access bits
     *
     * Bits can have values from 0 to 2 (0 - not set, 1 - direct set, 2 - inherited)
     * For deny values are negative
     */
    private static function getEstimatedUserAccessForObject($object_id, $user_id = false)
    {
        $is_owner = false;

        /* if no user is specified as parameter then calculating for current loged user */
        if ($user_id === false) {
            $user_id = User::getId();
        }

        /* prepearing result array (filling it with zeroes)*/
        $rez = array( array_fill(0, 12, 0), array_fill(0, 12, 0) );

        $user_group_ids = array($user_id);
        $everyoneGroupId = static::getSystemGroupId('everyone');
        if (($user_id !== $everyoneGroupId) && !empty($everyoneGroupId)) {
            $user_group_ids[] = $everyoneGroupId;
        }

        /* getting object ids that have inherit set to true */
        $ids = array();
        $res = DB\dbQuery(
            'SELECT t.oid, ts.`set`
            FROM tree t
            JOIN tree_info ti on t.id = ti.id
            LEFT JOIN tree_acl_security_sets ts on ti.security_set_id = ts.id
            WHERE t.id = $1',
            $object_id
        );

        if ($r = $res->fetch_assoc()) {
            $ids = explode(',', $r['set']);
            $ids = array_filter($ids, 'is_numeric');
            $is_owner = ($user_id == $r['oid']);
        } else {
            throw new \Exception(L\get('Object_not_found'), 1);
        }
        $res->close();

        /* reversing array for iterations from object to top parent */
        $ids = array_reverse($ids);

        /* getting group ids where passed $user_id is a member*/
        $res = DB\dbQuery(
            'SELECT DISTINCT group_id
            FROM users_groups_association
            WHERE user_id = $1',
            $user_id
        );

        while ($r = $res->fetch_assoc()) {
            if (!in_array($r['group_id'], $user_group_ids)) {
                $user_group_ids[] = $r['group_id'];
            }
        }
        $res->close();
        /* end of getting group ids where passed $user_id is a member*/

        $acl_order = array_flip($ids);
        $acl = array();

        // selecting access list set for our path ids
        $res = DB\dbQuery(
            'SELECT
                node_id
                ,user_group_id
                ,allow
                ,deny
            FROM tree_acl
            WHERE node_id IN (0'.implode(',', $ids).')
                AND user_group_id IN ('.implode(',', $user_group_ids).')'
        );

        while ($r = $res->fetch_assoc()) {
            $acl[$acl_order[$r['node_id']]][$r['user_group_id']] = array($r['allow'], $r['deny']);
        }
        $res->close();

        /* now iterating the $acl table and determine final set of bits/**/
        $set_bits = 0;
        $i=0;

        ksort($acl, SORT_NUMERIC);
        reset($acl);

        while (( current($acl) !== false ) && ($set_bits < 12)) {
            $i = key($acl);
            $inherited = ($i > 0);
            $allowDirectAccess = array_fill(0, 12, 0);
            /* check firstly if direct access is specified for passed user_id */
            if (!empty($acl[$i][$user_id])) {
                $deny = intval($acl[$i][$user_id][1]);
                for ($j=0; $j < sizeof($rez[1]); $j++) {
                    if (empty($rez[0][$j]) && empty($rez[1][$j]) && ($deny & 1)) {
                        $rez[1][$j] = -(1 + $inherited);
                        $set_bits++;
                    }
                    $deny = $deny >> 1;
                }
                $allow = intval($acl[$i][$user_id][0]);
                for ($j=0; $j < sizeof($rez[0]); $j++) {
                    if (empty($rez[0][$j]) && empty($rez[1][$j]) && ($allow & 1)) {
                        $rez[0][$j] = (1 + $inherited);
                        $allowDirectAccess[$j] = (1 + $inherited);
                        $set_bits++;
                    }
                    $allow = $allow >> 1;
                }

                /* if we have direct access specified to requested user for input object_id
                then return just this direct access  and exclude any other access at the same level (for our object_id) */
                if (isset($acl_order[$object_id]) && ($acl_order[$object_id] == $i)) {
                    next($acl);
                    continue;
                }
            }

            if (!empty($acl[$i])) {
                foreach ($acl[$i] as $key => $value) {
                    if (($key == $user_id) || ($key == $everyoneGroupId)) {
                        //skip direct access setting because analized above and everyone group id will be analized last
                        continue;
                    }
                    $deny = intval($value[1]);

                    for ($j=0; $j < sizeof($rez[1]); $j++) {
                        //set deny access only if not set directly for that credential allow access
                        if (empty($rez[0][$j]) && empty($rez[1][$j]) && ($deny & 1) && empty($allowDirectAccess[$j])) {
                            $rez[1][$j] = -(1 + $inherited);
                            $set_bits++;
                        }
                        $deny = $deny >> 1;
                    }
                    $allow = intval($value[0]);
                    for ($j=0; $j < sizeof($rez[0]); $j++) {
                        if (empty($rez[0][$j]) && empty($rez[1][$j]) && ($allow & 1)) {
                            $rez[0][$j] = (1 + $inherited);
                            $set_bits++;
                        }
                        $allow = $allow >> 1;
                    }
                }
            }

            // analize for everyone group id if set, but only for higher levels (inherited parents)
            if (!empty($acl[$i][$everyoneGroupId])) {
                $value = $acl[$i][$everyoneGroupId];
                $deny = intval($value[1]);
                for ($j=0; $j < sizeof($rez[1]); $j++) {
                    //set deny access only if not set directly for that credential allow access
                    if (empty($rez[0][$j]) && empty($rez[1][$j]) && ($deny & 1) && empty($allowDirectAccess[$j])) {
                        $rez[1][$j] = -(1 + $inherited);
                        $set_bits++;
                    }
                    $deny = $deny >> 1;
                }
                $allow = intval($value[0]);
                for ($j=0; $j < sizeof($rez[0]); $j++) {
                    if (empty($rez[0][$j]) && empty($rez[1][$j]) && ($allow & 1)) {
                        $rez[0][$j] = (1 + $inherited);
                        $set_bits++;
                    }
                    $allow = $allow >> 1;
                }
            }

            next($acl);
        }
        if ($is_owner) {
            $rez[0][static::$CAN_LIST_FOLDERS] = 1;
            $rez[0][static::$CAN_CREATE_FOLDERS] = 1;
            $rez[0][static::$CAN_CREATE_FILES] = 1;
            $rez[0][static::$CAN_CREATE_ACTIONS] = 1;
            $rez[0][static::$CAN_CREATE_TASKS] = 1;
            $rez[0][static::$CAN_READ] = 1;
            $rez[0][static::$CAN_WRITE] = 1;
            $rez[0][static::$CAN_DELETE] = 1;
            $rez[0][static::$CAN_CHANGE_PERMS] = 1;
            $rez[0][static::$CAN_TAKE_OWNERSHIP] = 1;
            $rez[0][static::$CAN_DOWNLOAD] = 1;

            $rez[1][static::$CAN_LIST_FOLDERS] = 0;
            $rez[1][static::$CAN_CREATE_FOLDERS] = 0;
            $rez[1][static::$CAN_CREATE_FILES] = 0;
            $rez[1][static::$CAN_CREATE_ACTIONS] = 0;
            $rez[1][static::$CAN_CREATE_TASKS] = 0;
            $rez[1][static::$CAN_READ] = 0;
            $rez[1][static::$CAN_WRITE] = 0;
            $rez[1][static::$CAN_DELETE] = 0;
            $rez[1][static::$CAN_CHANGE_PERMS] = 0;
            $rez[1][static::$CAN_TAKE_OWNERSHIP] = 0;
            $rez[1][static::$CAN_DOWNLOAD] = 0;
        }

        return $rez;
    }

    /**
     * get specific access for an object
     * @param  int $object_id
     * @param  int $access_bit_index access bits are defined at the top of this class
     * @param  int $user_id
     * @return int -2 inherited deny, -1 - deny, 0 - not set, 1 - allow, 2 - inherited allow
     */
    public static function getAccessBitForObject($object_id, $access_bit_index, $user_id = false)
    {
        $rez = null;

        if ($user_id === false) {
            $user_id = User::getId();
        }

        $eventParams = array(
            'object_id' => $object_id
            ,'access_bit' => $access_bit_index
            ,'user_id' => $user_id
            ,'result' => &$rez
        );
        fireEvent('beforeGetAccessForObject', $eventParams);

        if (is_null($rez)) {
            $accessArray = static::getEstimatedUserAccessForObject($object_id, $user_id);

            if (!empty($accessArray[0][$access_bit_index])) {
                $rez = $accessArray[0][$access_bit_index];
            } elseif (!empty($accessArray[1][$access_bit_index])) {
                $rez = $accessArray[1][$access_bit_index];
            }
        }

        fireEvent('getAccessForObject', $eventParams);

        if (is_null($rez)) {
            $rez = 0;
        }

        return $rez;
    }

    /**
     * Shortcut function for checking list folders access
     * @param  int     $object_id
     * @param  int     $user_group_id
     * @return boolean
     */
    public static function canListFolderOrReadData($object_id, $user_group_id = false)
    {
        return (
            Security::isAdmin() ||
            (Security::getAccessBitForObject($object_id, static::$CAN_LIST_FOLDERS, $user_group_id) > 0)
        );
    }

    /**
     * Shortcut function for checking create folders access
     * @param  int     $object_id
     * @param  int     $user_group_id
     * @return boolean
     */
    public static function canCreateFolders($object_id, $user_group_id = false)
    {
        return (
            Security::isAdmin() ||
            (Security::getAccessBitForObject($object_id, static::$CAN_CREATE_FOLDERS, $user_group_id) > 0)
        );
    }

    /**
     * Shortcut function for checking create files access
     * @param  int     $object_id
     * @param  int     $user_group_id
     * @return boolean
     */
    public static function canCreateFiles($object_id, $user_group_id = false)
    {
        return (
            Security::isAdmin() ||
            (Security::getAccessBitForObject($object_id, static::$CAN_CREATE_FILES, $user_group_id) > 0)
        );
    }

    /**
     * Shortcut function for checking create actions access
     * @param  int     $object_id
     * @param  int     $user_group_id
     * @return boolean
     */
    public static function canCreateActions($object_id, $user_group_id = false)
    {
        return (
            Security::isAdmin() ||
            (Security::getAccessBitForObject($object_id, static::$CAN_CREATE_ACTIONS, $user_group_id) > 0)
        );
    }

    /**
     * Shortcut function for checking create tasks access
     * @param  int     $object_id
     * @param  int     $user_group_id
     * @return boolean
     */
    public static function canCreateTasks($object_id, $user_group_id = false)
    {
        return (
            Security::isAdmin() ||
            (Security::getAccessBitForObject($object_id, static::$CAN_CREATE_TASKS, $user_group_id) > 0)
        );
    }

    /**
     * Shortcut function for checking read access
     * @param  int     $object_id
     * @param  int     $user_group_id
     * @return boolean
     */
    public static function canRead($object_id, $user_group_id = false)
    {
        return (
            Security::isAdmin() ||
            (Security::getAccessBitForObject($object_id, static::$CAN_READ, $user_group_id) > 0)
        );
    }

    /**
     * Shortcut function for checking write access
     * @param  int     $object_id
     * @param  int     $user_group_id
     * @return boolean
     */
    public static function canWrite($object_id, $user_group_id = false)
    {
        return (
            Security::isAdmin() ||
            (Security::getAccessBitForObject($object_id, static::$CAN_WRITE, $user_group_id) > 0)
        );
    }

    /**
     * Shortcut function for checking delete childs access
     * @param  int     $object_id
     * @param  int     $user_group_id
     * @return boolean
     */
    public static function canDeleteChilds($object_id, $user_group_id = false)
    {
        return (
            Security::isAdmin() ||
            (Security::getAccessBitForObject($object_id, static::$CAN_DELETE_CHILDS, $user_group_id) > 0)
        );
    }

    /**
     * Shortcut function for checking delete access
     * @param  int     $object_id
     * @param  int     $user_group_id
     * @return boolean
     */
    public static function canDelete($object_id, $user_group_id = false)
    {
        return (
            Security::isAdmin() ||
            (Security::getAccessBitForObject($object_id, static::$CAN_DELETE, $user_group_id) > 0)
        );
    }

    /**
     * Shortcut function for checking change permissions access
     * @param  int     $object_id
     * @param  int     $user_group_id
     * @return boolean
     */
    public static function canChangePermissions($object_id, $user_group_id = false)
    {
        return (
            Security::isAdmin() ||
            (Security::getAccessBitForObject($object_id, static::$CAN_CHANGE_PERMS, $user_group_id) > 0)
        );
    }

    /**
     * Shortcut function for checking take ownership access
     * @param  int     $object_id
     * @param  int     $user_group_id
     * @return boolean
     */
    public static function canTakeOwnership($object_id, $user_group_id = false)
    {
        return (
            Security::isAdmin() ||
            (Security::getAccessBitForObject($object_id, static::$CAN_TAKE_OWNERSHIP, $user_group_id) > 0)
        );
    }

    /**
     * Shortcut function for checking download access
     * @param  int     $object_id
     * @param  int     $user_group_id
     * @return boolean
     */
    public static function canDownload($object_id, $user_group_id = false)
    {
        return (
            Security::isAdmin() ||
            (Security::getAccessBitForObject($object_id, static::$CAN_DOWNLOAD, $user_group_id) > 0)
        );
    }

    /**
     * Shortcut function for getting the list of direct access rules (not inherited) set for an item
     * @param  array     client side json request
     * @return json response
     */
    public function getObjectDirectAcl($p)
    {
        $rez = $this->getObjectAcl($p, false);

        return $rez;
    }

    /**
     * add a security rule for an item
     * @param  array $p json request
     * @return json  response
     */
    public function addObjectAccess($p)
    {
        $rez = array('success' => true, 'data' => array());
        if (empty($p['data'])) {
            return $rez;
        }

        if (!Security::isAdmin() && !Security::canChangePermissions($p['id'])) {
            throw new \Exception(L\get('Access_denied'));
        }

        DB\dbQuery(
            'INSERT INTO tree_acl (node_id, user_group_id, cid, uid)
            VALUES ($1, $2, $3, $3)
            ON DUPLICATE KEY
            UPDATE id = last_insert_id(id)
                ,uid = $3',
            array(
                $p['id']
                ,$p['data']['user_group_id']
                ,User::getId()
            )
        );

        $p['data']['id'] = $p['data']['user_group_id'];
        $rez['data'][] = $p['data'];
        \CB\debug($rez);
        Security::calculateUpdatedSecuritySets();
        Solr\Client::runBackgroundCron();

        return $rez;
    }

    /**
     * update a security rule for an item
     * @param  array $p json request
     * @return json  response
     */
    public function updateObjectAccess($p)
    {
        if (!Security::isAdmin() && !Security::canChangePermissions($p['id'])) {
            throw new \Exception(L\get('Access_denied'));
        }

        $allow = explode(',', $p['data']['allow']);
        $deny = explode(',', $p['data']['deny']);
        for ($i=0; $i < 12; $i++) {
            $allow[$i] = ($allow[$i] == 1) ? '1' : '0';
            $deny[$i] = ($deny[$i] == -1) ? '1' : '0';
        }
        $allow = array_reverse($allow);
        $deny = array_reverse($deny);
        $allow = bindec(implode('', $allow));
        $deny = bindec(implode('', $deny));
        DB\dbQuery(
            'INSERT INTO tree_acl (
                node_id
                ,user_group_id
                ,allow
                ,deny
                ,cid)
            VALUES($1
                 ,$2
                 ,$3
                 ,$4
                 ,$5) ON DUPLICATE KEY
            UPDATE allow = $3
                    ,deny = $4
                    ,uid = $5
                    ,udate = CURRENT_TIMESTAMP',
            array(
                $p['id']
                ,$p['data']['user_group_id']
                ,$allow
                ,$deny
                ,User::getId()
            )
        );

        Security::calculateUpdatedSecuritySets();

        Solr\Client::runBackgroundCron();

        $p['data']['id'] = $p['data']['user_group_id'];

        return array('succes' => true, 'data' => $p['data'] );
    }

    /**
     * remove a security rule for an item
     * @param  array $p json request
     * @return array json  response
     */
    public function destroyObjectAccess($p)
    {
        if (empty($p['data'])) {
            return;
        }
        if (!Security::isAdmin() && !Security::canChangePermissions($p['id'])) {
            throw new \Exception(L\get('Access_denied'));
        }
        DB\dbQuery('DELETE FROM tree_acl WHERE node_id = $1 AND user_group_id = $2', array($p['id'], $p['data']['id']));

        Security::calculateUpdatedSecuritySets();
        Solr\Client::runBackgroundCron();

        return array('success' => true, 'data'=> array());
    }

    /**
     * setting security inheritance flag for an item
     *
     * @param array $p {
     *     @type int      $id    id of tree node
     *     @type boolean  $inherit    set inherit to true or false
     *     @type string   $copyRules   when removing inheritance ($inherit = false)
     *                                 then this value could be set to 'yes' or 'no'
     *                                 for copying inherited rules to current node
     * }
     *
     */
    public function setInheritance($p)
    {
        /* check input params */
        if (empty($p['id']) ||
            !isset($p['inherit']) ||
            !is_numeric($p['id']) ||
            !is_bool($p['inherit'])
        ) {
            throw new \Exception(L\get('Wrong_input_data'));
        }
        /* end of check input params */

        if (!Security::isAdmin() && !Security::canChangePermissions($p['id'])) {
            throw new \Exception(L\get('Access_denied'));
        }

        /* checking if current inherit value is not already set to requested state */
        $inherit_acl = false;

        $r = DM\Tree::read($p['id']);

        if (!empty($r)) {
            $inherit_acl = $r['inherit_acl'];

        } else {
            throw new \Exception(L\get('Object_not_found'));
        }

        if ($inherit_acl == $p['inherit']) {
            return array('success' => false);
        }
        /* end of checking if current inherit value is not already set to requested state */

        // make pre update changes
        if ($p['inherit']) {
            DB\dbQuery('DELETE from tree_acl WHERE node_id = $1', $p['id']);

        } else {
            switch (@$p['copyRules']) {
                case 'yes':
                    //copy all inherited rules to current object
                    $acl = $this->getObjectAcl($p);
                    foreach ($acl['data'] as $rule) {
                        $allow = explode(',', str_replace('2', '1', $rule['allow']));
                        $deny = explode(',', str_replace('2', '1', $rule['deny']));
                        for ($i=0; $i < 12; $i++) {
                            $allow[$i] = ($allow[$i] == 1) ? '1' : '0';
                            $deny[$i] = ($deny[$i] == -1) ? '1' : '0';
                        }
                        $allow = array_reverse($allow);
                        $deny = array_reverse($deny);
                        $allow = bindec(implode('', $allow));
                        $deny = bindec(implode('', $deny));
                        DB\dbQuery(
                            'INSERT INTO tree_acl (
                                node_id
                                ,user_group_id
                                ,allow
                                ,deny
                                ,cid)
                            VALUES($1
                                 ,$2
                                 ,$3
                                 ,$4
                                 ,$5) ON duplicate KEY
                            UPDATE allow = $3
                                    ,deny = $4
                                    ,uid = $5
                                    ,udate = CURRENT_TIMESTAMP',
                            array(
                                $p['id']
                                ,$rule['id']
                                ,$allow
                                ,$deny
                                ,User::getId()
                            )
                        );
                    }
                    break;
                default:
                    DB\dbQuery('DELETE from tree_acl WHERE node_id = $1', $p['id']);
                    break;
            }
        }

        // updating inherit flag for the object
        DM\Tree::update(
            array(
                'id' => $p['id'],
                'inherit_acl' => intval($p['inherit'])
            )
        );

        Security::calculateUpdatedSecuritySets();

        Solr\Client::runBackgroundCron();

        return array('success' => true, 'data'=> array());
    }

    /**
     * remove security rules from child items
     *
     * @param array json request
     * @return array json  response
     */
    public function removeChildPermissions($p)
    {

        if (!Security::isAdmin()) {
            throw new \Exception(L\get('Access_denied'));
        }

        $pids = Objects::getPids($p['id'], false);
        $pids = implode(',', $pids);

        $child_ids = array();

        // selecting childs with accesses
        $res = DB\dbQuery(
            'SELECT id
            FROM tree_info
            WHERE pids like $1 and acl_count > 0',
            $pids.',%'
        );

        while ($r = $res->fetch_assoc()) {
            $child_ids[] = $r['id'];
        }
        $res->close();

        //remove security rules for childs
        if (!empty($child_ids)) {
            DB\dbQuery('DELETE FROM tree_acl WHERE node_id in ('.implode(',', $child_ids).')');
            // update inherit flag
            DB\dbQuery('UPDATE tree SET inherit_acl = 1 WHERE id in ('.implode(',', $child_ids).')');
        }

        Solr\Client::runBackgroundCron();

        return array('success' => true);
    }

    /**
     * copy security rules from source node to target node from tree
     * @param  int  $sourceNodeId
     * @param  int  $targetNodeId
     * @return void
     */
    public static function copyNodeAcl($sourceNodeId, $targetNodeId)
    {
        DB\dbQuery(
            'INSERT INTO `tree_acl`
            (`node_id`
            ,`user_group_id`
            ,`allow`
            ,`deny`
            ,`cid`
            ,`cdate`
            ,`uid`
            ,`udate`)
            SELECT
                $2
                ,`user_group_id`
                ,`allow`
                ,`deny`
                ,`cid`
                ,`cdate`
                ,`uid`
                ,`udate`
            FROM `tree_acl`
            WHERE node_id = $1',
            array(
                $sourceNodeId
                ,$targetNodeId
            )
        );
    }

    /* end of objects acl methods*/

    /**
     * return set ids for a user that have access on specified bit
     * @param  boolean         $user_id
     * @param  integer         $access_bit_index default static::$CAN_READ
     * @param  integer | array $pids
     * @return array           security set ids
     */
    public static function getSecuritySets ($user_id = false, $access_bit_index = null, $pids = null)
    {
        $rez = array();
        $sets = array();

        if (empty($access_bit_index)) {
            $access_bit_index = static::$CAN_READ;
        }

        if (empty($user_id)) {
            $user_id = User::getId();
        }

        $everyoneGroupId = static::getSystemGroupId('everyone');

        $res = DB\dbQuery(
            'SELECT security_set_id, user_id, bit'.$access_bit_index.' `access`
            FROM `tree_acl_security_sets_result`
            WHERE user_id IN ($1, $2)',
            array(
                $user_id
                ,$everyoneGroupId
                )
        );

        while ($r = $res->fetch_assoc()) {
            $sets[$r['security_set_id']][$r['user_id']] = $r['access'];
        }
        $res->close();

        $rez = array();
        foreach ($sets as $set_id => $set) {
            if (!empty($set[$user_id])
                || (!isset($set[$user_id]) && !empty($set[$everyoneGroupId]))
            ) {
                $rez[] = $set_id;
            }
        }

        //filter sets if pids specified
        if (!empty($pids)) {
            $pids = Util\toNumericArray($pids);
        }

        if (!empty($rez) && !empty($pids)) {
            //select all pids of given pid ids
            $res = DB\dbQuery(
                'SELECT pids
                FROM tree_info
                WHERE id in (' . implode(',', $pids) . ')',
                array()
            );

            while ($r = $res->fetch_assoc()) {
                $ids = explode(',', $r['pids']);
                foreach ($ids as $id) {
                    if (!in_array($id, $pids)) {
                        $pids[] = $id;
                    }
                }
            }
            $res->close();

            //select distinct set nodes
            $nodes = array();
            $res = DB\dbQuery(
                'SELECT id, `set`
                FROM tree_acl_security_sets
                WHERE id in (' . implode(',', $rez) . ')'
            );

            while ($r = $res->fetch_assoc()) {
                $ids = explode(',', $r['set']);

                foreach ($ids as $id) {
                    $nodes[$id][] = $r['id'];
                }
            }
            $res->close();

            $rez = array();

            //now select pids of collected nodes and filter only sets
            //that are for child nodes of the pids
            $recs = DM\TreeInfo::readByIds(array_keys($nodes));

            foreach ($recs as $r) {
                $ids = explode(',', $r['pids']);
                $intersection = array_intersect($pids, $ids);

                if (!empty($intersection)) {
                    foreach ($nodes[$r['id']] as $setId) {
                        $rez[$setId] = 1;
                    }
                }
            }

            $rez = array_keys($rez);
        }

        return $rez;
    }

    /**
     * recalculate security sets marked as updated in db
     * @param  boolean $onlyForUserId specific user or all if false
     * @return void
     */
    public static function calculateUpdatedSecuritySets($onlyForUserId = false)
    {

        if (Cache::get('calculatingSecuritySets', false)) {
            return;
        }

        //set a flag to avoid double call to this function
        Cache::set('calculatingSecuritySets', true);

        DB\startTransaction();
        $res = DB\dbQuery(
            'SELECT id
            FROM tree_acl_security_sets
            WHERE updated = 1'
        );

        while ($r = $res->fetch_assoc()) {
            //calculate for all even if there are sets for non existing obejcts
            try {
                Security::updateSecuritySet($r['id'], $onlyForUserId);
            } catch (\Exception $e) {

            }
        }
        $res->close();
        DB\commitTransaction();

        Cache::remove('calculatingSecuritySets');
    }

    /**
     * update a security set
     * @param  int  $setId
     * @param  int  $onlyForUserId
     * @return void
     */
    public static function updateSecuritySet($setId, $onlyForUserId = false)
    {
        /* get set */
        $set = '';
        $res = DB\dbQuery(
            'SELECT `set`
            FROM tree_acl_security_sets
            WHERE id = $1',
            $setId
        );

        if ($r = $res->fetch_assoc()) {
            $set = $r['set'];
        }
        $res->close();

        /* end of get set*/

        $objIds = explode(',', $set);
        $everyoneGroupId = static::getSystemGroupId('everyone');
        $users = array();
        $updatingUser = false;

        /* iterate the full set of access credentials(users and/or groups)
        and estimate access for every user including everyone group */
        if (!empty($set)) {
            $objectId = $objIds[sizeof($objIds) -1];

            $groupUsers = array();
            if (!empty($onlyForUserId)) {
                $groupUsers = static::getGroupUserIds($onlyForUserId);

                if (empty($groupUsers)) {
                    $updatingUser = true;
                    $users[$onlyForUserId] = static::getEstimatedUserAccessForObject($objectId, $onlyForUserId);
                }
            }

            if (!$updatingUser) {
                $res = DB\dbQuery(
                    'SELECT DISTINCT
                        u.id
                        ,u.`type`
                    FROM tree_acl a
                    JOIN users_groups u on a.user_group_id = u.id
                    WHERE a.node_id in(0' . implode(',', $objIds).')
                    ORDER BY u.`type`'
                );

                while ($r = $res->fetch_assoc()) {
                    $groupUsers = array();
                    if (($r['id'] == $everyoneGroupId) || ($r['type'] == 2)) {
                        $groupUsers[] = $r['id'];
                    } else {
                        $groupUsers = Security::getGroupUserIds($r['id']);
                    }

                    foreach ($groupUsers as $userId) {
                        if (empty($users[$userId])) {
                            $users[$userId] = static::getEstimatedUserAccessForObject($objectId, $userId);
                        }
                    }
                }
                $res->close();
            }
        }
        /* end of iterate the full set of access credentials(users and/or groups) and estimate access for every user including everyone group */

        /* update set in database */

        $res = DB\dbQuery(
            'DELETE
            FROM tree_acl_security_sets_result
            WHERE security_set_id = $1
                and (ISNULL($2) OR ($2 = user_id))',
            array(
                $setId
                ,$updatingUser
                    ? $onlyForUserId
                    : null
            )
        );

        $sql = 'INSERT INTO tree_acl_security_sets_result
                (security_set_id
                ,user_id
                ,bit0
                ,bit1
                ,bit2
                ,bit3
                ,bit4
                ,bit5
                ,bit6
                ,bit7
                ,bit8
                ,bit9
                ,bit10
                ,bit11)
            VALUES ($1
                ,$2
                ,$3
                ,$4
                ,$5
                ,$6
                ,$7
                ,$8
                ,$9
                ,$10
                ,$11
                ,$12
                ,$13
                ,$14)';

        foreach ($users as $userId => $access) {
            $params = array($setId, $userId);
            for ($i=0; $i < sizeof($access[0]); $i++) {
                $params[] = (empty($access[1][$i]) && ($access[0][$i] >0)) ? 1 : 0;
            }

            $res = DB\dbQuery($sql, $params);
        }

        $res = DB\dbQuery(
            'UPDATE tree_acl_security_sets
            SET updated = 0
            WHERE id = $1',
            $setId
        );
        /* end of update set in database */
    }

    /**
     * Retreive a system group id by its name
     * (only everyone left after removing "system" group)
     *
     * @return int
     */
    public static function getSystemGroupId($groupName)
    {
        if (!Cache::exist('group_id_' . $groupName)) {
            $id = DM\Group::getIdByName($groupName);
            Cache::set('group_id_' . $groupName, $id);
        }

        return Cache::get('group_id_' . $groupName);
    }

    /**
     * Get an array of user ids associated to the given group
     *
     * @param  int   $groupId
     * @return array
     */
    public static function getGroupUserIds($groupId)
    {
        $rez = DM\UsersGroups::getGroupUserIds($groupId);

        return $rez;
    }

    /**
     * Get the list of active users with basic data
     * @return array
     */
    public static function getActiveUsers()
    {
        $rez = array(
            'success' => true,
            'data' => array()
        );

        // $photosPath = Config::get('photos_path');

        $users = DM\UsersGroups::getAvailableUsers();

        foreach ($users as $r) {
            $r['user'] = $r['name'];
            $r['name'] = User::getDisplayName($r);

            $r['photo'] = User::getPhotoParam($r);

            $rez['data'][] = $r;
        }

        return $rez;
    }
    /* ----------------------------------------------------  OLD METHODS ------------------------------------------ */

    /**
     * Check if userId (or current loged user) is an administrator
     *
     * @param  int     $userId
     * @return boolean
     */
    public static function isAdmin($userId = false)
    {
        if ($userId == false) {
            $userId = User::getId();
        }

        $var_name = 'is_admin'.$userId;

        if (!Cache::exist($var_name)) {
            Cache::set(
                $var_name,
                (DM\Users::getIdByName('root') == $userId)
            );
        }

        return Cache::get($var_name);
    }

    /**
     * Check if user_id (or current loged user) can manage users or groups
     *
     * @param  int     $user_id
     * @return boolean
     */
    public static function canManage($userId = false)
    {
        return (Security::canAddUser($userId) || Security::canAddGroup($userId));
    }

    /**
     * Check if current loged user is owner for given user id
     *
     * @param  int     $userId
     * @return boolean
     */
    public static function isUsersOwner($userId)
    {
        return (User::getId() == DM\Users::getOwnerId($userId));
    }

    /**
     * Check if user_id (or current loged user) can add users
     *
     * @param  int     $userId
     * @return boolean
     */
    public static function canAddUser($userId = false)
    {
        if (Security::isAdmin($userId)) {
            return true;
        }

        $userData = ($userId === false)
            ? $_SESSION['user']
            : User::getPreferences($userId);

        return !empty($userData['cfg']['canAddUsers']);
    }

    /**
     * Check if user_id (or current loged user) can add groups
     *
     * @param  int     $userId
     * @return boolean
     */
    public static function canAddGroup($userId = false)
    {
        if (Security::isAdmin($userId)) {
            return true;
        }

        $userData = ($userId === false)
            ? $_SESSION['user']
            : User::getPreferences($userId);

        return !empty($userData['cfg']['canAddGroups']);
    }

    /**
     * Check if current loged user can edit another user
     *
     * @param  int     $userId
     * @return boolean
     */
    public static function canEditUser($user_id)
    {
        return (Security::isAdmin() || Security::isUsersOwner($user_id) || (User::getId() == $user_id));
    }

    /**
     * function to check if a user cam manage task
     *
     * This function returns true if specified user can manage/update specified task.
     * User can manage a task if he is Administrator, Creator of the task
     * or is one of the responsible task users.
     *
     * @param  int     $taskId id of the task to be checked
     * @param  int     $userId id of the user to be checked
     * @return boolean returns true in case of the user can manage the task
     */
    public static function canManageTask($taskId, $userId = false)
    {
        $rez = false;

        if ($userId == false) {
            $userId = User::getId();
        }

        $task = Objects::getCachedObject($taskId);

        $data = $task->getData();

        $rez = ($data['cid'] == $userId) ||
            in_array($userId, $data['sys_data']['task_u_ongoing']) ||
            in_array($userId, $data['sys_data']['task_u_done']);

        if (!$rez) {
            $rez = Security::isAdmin($userId);
        }

        return $rez;
    }
}
