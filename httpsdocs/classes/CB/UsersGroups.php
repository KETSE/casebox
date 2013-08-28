<?php

namespace CB;

class UsersGroups
{
    /**
     * Get the child list to be displayed in user management window in left tree
     */
    public function getChildren($p)
    { //CHECKED
        $rez = array();
        if (!Security::canManage()) {
            throw new \Exception(L\Access_denied);
        }
        $path = explode('/', $p->path);
        $id = array_pop($path);
        $node_type = null;

        if (is_numeric($id)) {
            $sql = 'select type from users_groups where id = $1';
            $res = DB\dbQuery($sql, $id) or die(DB\dbQueryError());
            if ($r = $res->fetch_row()) {
                $node_type = $r[0];
            }
            $res->close();
        }

        if ($id == -1) { // users out of a group
            $sql = 'SELECT id `nid`
                     , u.cid
                     , name
                     , first_name
                     , last_name
                     , sex
                     , `enabled`
                FROM users_groups u
                LEFT JOIN users_groups_association a ON u.id = a.user_id
                WHERE u.`type` = 2
                    AND u.did IS NULL
                    AND a.group_id IS NULL
                ORDER BY 3, 2';
            $res = DB\dbQuery($sql, array()) or die(DB\dbQueryError());
            while ($r = $res->fetch_assoc()) {
                $r['loaded'] = true;
                $rez[] = $r;
            }
            $res->close();
        } elseif (is_null($node_type)) { /* root node childs*/
            $sql = 'SELECT id `nid`, name, first_name, last_name, `type`, `system`
                  , (SELECT count(*)
                     FROM users_groups_association a
                     JOIN users_groups u ON a.user_id = u.id
                     AND u.did IS NULL
                     WHERE group_id = g.id) `loaded`
                FROM users_groups g
                WHERE `type` = 1
                    AND `system` = 0
                ORDER BY 3, 2';
            $res = DB\dbQuery($sql, array()) or die(DB\dbQueryError());
            while ($r = $res->fetch_assoc()) {
                $r['iconCls'] = 'icon-users';
                $r['expanded'] = true;

                $rez[] = $r;
            }
            $res->close();
            $rez[] = array('nid' => -1
                ,'title' => L\Users_without_group
                ,'iconCls' => 'icon-users'
                ,'type' => 1
                ,'expanded' => true
            );
        } else {// group users
            $sql = 'SELECT u.id `nid`
                     , u.cid
                     , u.name
                     , first_name
                     , last_name
                     , sex
                     , enabled
                FROM users_groups_association a
                JOIN users_groups u ON a.user_id = u.id
                WHERE a.group_id = $1
                    AND u.did IS NULL';
            $res = DB\dbQuery($sql, $id) or die(DB\dbQueryError());
            while ($r = $res->fetch_assoc()) {
                $r['loaded'] = true;
                $rez[] = $r;
            }
            $res->close();
        }

        $pid = empty($id) ? 'is null' : ' = '.intval($id);

        /* collapse first and last names into title */
        for ($i=0; $i < sizeof($rez); $i++) {
            if (!empty($rez[$i]['first_name']) && !empty($rez[$i]['last_name'])) {
                $rez[$i]['title'] = trim(
                    $rez[$i]['first_name'].
                    ' '.
                    $rez[$i]['last_name']
                );
            }
            unset($rez[$i]['first_name']);
            unset($rez[$i]['last_name']);
        }
        /* end of collapse first and last names into title */

        return $rez;
    }

    /**
     * Associating a user to a group
     */
    public function associate($user_id, $group_id)
    {
        if (!Security::canManage()) {
            throw new \Exception(L\Access_denied);
        }
        $res = DB\dbQuery(
            'SELECT user_id
            FROM users_groups_association
            WHERE user_id = $1
                AND group_id = $2',
            array($user_id, $group_id)
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_row()) {
            throw new \Exception(L\UserAlreadyInOffice);
        }
        $res->close();
        DB\dbQuery(
            'INSERT INTO users_groups_association (user_id, group_id, cid)
            VALUES ($1
                  , $2
                  , $3)',
            array(
                $user_id
                ,$group_id
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        Security::calculateUpdatedSecuritySets();

        return array('success' => true);
    }

    /**
     * Deassociating a user from a group
     */
    public function deassociate($user_id, $group_id)
    {
        if (!Security::canManage()) {
            throw new \Exception(L\Access_denied);
        }
        $res = DB\dbQuery(
            'DELETE
            FROM users_groups_association
            WHERE user_id = $1
                AND group_id = $2',
            array($user_id, $group_id)
        ) or die(DB\dbQueryError());

        Security::calculateUpdatedSecuritySets();

        //return if the user is associated to another office, otherwize it shoul be added to Users out of office folder
        $outOfGroup = true;
        $res = DB\dbQuery(
            'SELECT group_id
            FROM users_groups_association
            WHERE user_id = $1 LIMIT 1',
            $user_id
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_row()) {
            $outOfGroup = false;
        }

        return array('success' => true, 'outOfGroup' => $outOfGroup);
    }

    /**
     * Add a new user
     */
    public function addUser($p)
    {
        if (!Security::canManage()) {
            throw new \Exception(L\Access_denied);
        }
        ////params: name, group_id
        $rez = array('success' => false, 'msg' => L\Missing_required_fields);
        $p->name = trim($p->name);
        if (empty($p->name) ||
            empty($p->password) ||
            (empty($p->confirm_password) ||
            ($p->password != $p->confirm_password))) {
            return $rez;
        }
        $user_id = 0;
        /*check user existance, if user already exists but is deleted then its record will be used for new user */
        $res = DB\dbQuery("select id from users_groups where name = $1 and did is NULL", $p->name) or die(DB\dbQueryError());
        if ($r = $res->fetch_row()) {
            throw new \Exception(L\User_exists);
        }
        $res->close();
        /*end of check user existance */

        DB\dbQuery(
            'INSERT INTO users_groups (`name`, first_name, last_name, `cid`, `password`, language_id, cdate, uid, email)
            VALUES($1
                ,$2
                ,$3
                ,$4
                ,MD5(CONCAT(\'aero\', $5))
                ,$6
                ,CURRENT_TIMESTAMP
                ,$4
                ,$7)
            ON DUPLICATE KEY
            UPDATE id = last_insert_id(id)
                ,`name` = $1
                ,`first_name` = $2
                ,`last_name` = $3
                ,`cid` = $4
                ,`password` = MD5(CONCAT(\'aero\', $5))
                ,last_login = NULL
                ,login_successful = NULL
                ,login_from_ip = NULL
                ,last_logout = NULL
                ,last_action_time = NULL
                ,enabled = 1
                ,cdate = CURRENT_TIMESTAMP
                ,did = NULL
                ,ddate = NULL
                ,language_id = $6
                ,uid = $4
                ,cdate = CURRENT_TIMESTAMP',
            array(
                $p->name
                ,$p->first_name
                ,$p->last_name
                ,$_SESSION['user']['id']
                ,$p->password
                ,LANGUAGE_INDEX
                ,$p->email
            )
        ) or die(DB\dbQueryError());
        if ($user_id = DB\dbLastInsertId()) {
            $rez = array('success' => true, 'data' => array('id' => $user_id));
            $p->id = $user_id;
        }

        DB\dbQuery(
            'DELETE
            FROM users_groups_data
            WHERE user_id = $1',
            $user_id
        ) or die(DB\dbQueryError());

        /* in case it was a deleted user we delete all old acceses */
        DB\dbQuery('delete from users_groups_association where user_id = $1', $user_id) or die(DB\dbQueryError());
        DB\dbQuery('delete from tree_acl where user_group_id = $1', $rez['data']['id']) or die(DB\dbQueryError());
        /* end of in case it was a deleted user we delete all old acceses */

        // associating user to group if group was specified
        if (isset($p->group_id) && is_numeric($p->group_id)) {
            DB\dbQuery(
                'INSERT INTO users_groups_association (user_id, group_id, cid)
                VALUES($1
                     , $2
                     , $3) ON duplicate KEY
                UPDATE cid = $3',
                array(
                    $user_id
                    ,$p->group_id
                    ,$_SESSION['user']['id']
                )
            ) or die(DB\dbQueryError());
            $rez['data']['group_id'] = $p->group_id;
        } else {
            $rez['data']['group_id'] = 0;
        }

        Security::calculateUpdatedSecuritySets();
        Solr\Client::runBackgroundCron();

        return $rez;
    }

    /**
     * Retreive all user emails specified in user details and concatenates them into a comma separated string into email field from users_groups table
     */
    private function updateUserEmails($user_id)
    {
        $emails = array();
        $res = DB\dbQuery('SELECT ud.value FROM templates t  JOIN templates_structure ts ON ts.template_id = t.id AND ts.name = \'email\' JOIN users_groups_data ud ON ts.id = ud.field_id and ud.user_id = $1 WHERE t.`type` = 6', $user_id) or die(DB\dbQueryError());
        while ($r = $res->fetch_row()) {
            if (!empty($r[0])) {
                $emails[] = $r[0];
            }
        }
        $res->close();
        $emails = empty($emails) ? null : implode(', ', $emails);
        DB\dbQuery('update users_groups set email = $1 where id = $2', array($emails, $user_id)) or die(DB\dbQueryError());
    }

    /**
     * Delete a user from user management window
     */
    public function deleteUser($user_id)
    {
        if (!Security::canManage()) {
            throw new \Exception(L\Access_denied);
        }
        $res = DB\dbQuery(
            'UPDATE users_groups
            SET did = $2
                , ddate = CURRENT_TIMESTAMP
            WHERE id = $1',
            array(
                $user_id
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError()); // and (cid = $2) !!!!

        //TODO: destroy user session if loged in
        return array('success' => DB\dbAffectedRows() ? true : false, 'data' => array($user_id, $_SESSION['user']['id']));
    }

    /**
     * Delete a group from user management window
     */
    public function deleteGroup($group_id)
    {
        if (!Security::canEditUser()) {
            throw new \Exception(L\Access_denied);
        }

        /* Delete group record. All security rules with this group wil be deleted by foreign key.
        On deleting a group also the users associations are deleted by the foreign key
        and corresponding security sets are marked, by trigger, as updated.
        */
        DB\dbQuery('delete from users_groups where id = $1 and `type` = 1', $group_id) or die(DB\dbQueryError());

        /* call the recalculation method for security sets. */
        Security::calculateUpdatedSecuritySets();

        return array('success' => true);
    }

    /**
     * Retreive user details data to be displayed in user details window
     */
    public function getUserData($p)
    {
        if (($_SESSION['user']['id'] != $p->data->id) && !Security::canManage()) {
            throw new \Exception(L\Access_denied);
        }
        $user_id = $p->data->id;
        $rez = array('success' => false, 'msg' => L\Wrong_id);

        $res = DB\dbQuery(
            'SELECT id
                ,cid
                ,name
                ,first_name
                ,last_name
                ,sex
                ,email
                ,enabled
                ,date_format(last_action_time,\''.$_SESSION['user']['cfg']['short_date_format'].' %H:%i\') last_action_time
                ,date_format(cdate,\''.$_SESSION['user']['cfg']['short_date_format'].' %H:%i\') `cdate`
                ,(SELECT l'.USER_LANGUAGE_INDEX.'
                    FROM users_groups
                    WHERE id = u.cid) `owner`
            FROM users_groups u
            WHERE id = $1 ',
            $user_id
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $r['title'] = trim($r['first_name'].' '.$r['last_name']);
            $rez = array('success' => true, 'data' => $r);
        }
        $res->close();
        if ($rez['success'] == false) {
            throw new \Exception(L\Wrong_id);
        }

        $rez['data']['template_id'] = User::getTemplateId();

        VerticalEditGrid::getData('users_groups', $rez['data']);

        return $rez;
    }

    /**
     * Save user details data from user details window
     */
    public function saveUserData($params)
    {
        $rez = array('success' => true);
        $data = json_decode($params['data']);

        if (!Security::canEditUser($data->id)) {
            throw new \Exception(L\Access_denied);
        }
        VerticalEditGrid::saveData('users_groups', $data);

        /* if updating current logged user then checking if interface params have changed */
        $interface_params_changed = false;
        if ($data->id == $_SESSION['user']['id']) {
            $res = DB\dbQuery('select '.CONFIG\LANGUAGE_FIELDS.', language_id, cfg from users_groups u where id = $1 ', $data->id) or die(DB\dbQueryError());
            if ($r = $res->fetch_assoc()) {
                // TODO: review
                $r['language'] = $GLOBALS['languages'][$r['language_id']-1];
                if (empty($r['long_date_format'])) {
                    $r['long_date_format'] = $GLOBALS['language_settings'][$r['language']]['long_date_format'];
                }
                if (empty($r['short_date_format'])) {
                    $r['short_date_format'] = $GLOBALS['language_settings'][$r['language']]['short_date_format'];
                }
                foreach ($r as $k => $v) {
                    if ($_SESSION['user'][$k] != $v) {
                        $interface_params_changed = true;
                        $_SESSION['user'][$k] = $v;
                    }
                }
            }
            $res->close();
            if ($interface_params_changed) {
                $rez['interface_params_changed'] = true;
            }
        }
        /* end of if updating current logged user then checking if interface params have changed */
        // $this->updateUserEmails($data->id);
        return $rez;
    }

    /**
     * Get access data for a user to be displayed in user management window
     */
    public function getAccessData($user_id = false)
    {
        if (!Security::canManage()) {
            throw new \Exception(L\Access_denied);
        }
        $user_id = $this->extractId($user_id);
        $rez = $this->getUserData((Object)array( 'data' => (object) array('id' => $user_id)));

        $rez['data']['groups'] = array();
        $sql = 'SELECT a.group_id from users_groups_association a where user_id = $1';
        $res = DB\dbQuery($sql, $user_id) or die(DB\dbQueryError());
        while ($r = $res->fetch_row()) {
            $rez['data']['groups'][] = $r[0];
        }
        $res->close();

        return $rez;
    }

    /**
     * Save access data specified for a user in UserManagement form (groups association)
     *
     *
     */
    public function saveAccessData($p)
    {
        if (!Security::canManage()) {
            throw new \Exception(L\Access_denied);
        }
        $p = (Array)$p;
        @$user_id = $this->extractId($p['id']);

        /* analize groups:
            - for newly associated groups the access should be updated
            - for deassociated groups the access also should be reviewed/**/

        /* get current user groups */
        $current_groups = UsersGroups::getGroupIdsForUser($user_id);
        $updating_groups = Util\toNumericArray(@$p['groups']);

        $new_groups = array_diff($updating_groups, $current_groups);
        $deleting_groups = array_diff($current_groups, $updating_groups);

        foreach ($new_groups as $group_id) {
            DB\dbQuery(
                'INSERT INTO users_groups_association (user_id, group_id, cid)
                VALUES($1
                     , $2
                     , $3)
                ON DUPLICATE KEY
                UPDATE uid = $3',
                array(
                    $user_id
                    ,$group_id
                    ,$_SESSION['user']['id']
                )
            ) or die(DB\dbQueryError());
        }

        if (!empty($deleting_groups)) {
            DB\dbQuery(
                'DELETE
                FROM users_groups_association
                WHERE user_id = $1
                    AND group_id IN ('.implode(', ', $deleting_groups).')',
                $user_id
            ) or die(DB\dbQueryError());
        }

        Security::calculateUpdatedSecuritySets();

        return array('success' => true);
    }

    /**
     * Get an array of group ids for specified user. If no user is passed then current loged user is analized.
     *
     */
    public static function getGroupIdsForUser($user_id = false)
    {
        if ($user_id === false) {
            $user_id = $_SESSION['user']['id'];
        }

        $groups = array();
        $sql = 'select group_id from users_groups_association where user_id = $1';
        $res = DB\dbQuery($sql, $user_id) or die( DB\dbQueryError() );
        while ($r = $res->fetch_row()) {
            $groups[] = $r[0];
        }
        $res->close();

        return $groups;
    }

    /**
     * Change user password.
     */
    public function changePassword($p)
    {
        /* passord could be changed by: admin, user owner, user himself */
        if (empty($p['password']) || ($p['password'] != $p['confirmpassword'])) {
            throw new \Exception(L\Wrong_input_data);
        }
        $user_id = $this->extractId($p['id']);

        /* check for old password if users changes password for himself */
        if ($_SESSION['user']['id'] == $user_id) {
            $res = DB\dbQuery('select id from users_groups where id = $1 and `password` = MD5(CONCAT(\'aero\', $2))', array($user_id, $p['currentpassword'])) or die(DB\dbQueryError());
            if (!$res->fetch_row()) {
                throw new \Exception(L\WrongCurrentPassword);
            }
            $res->close();
        }
        /* end of check for old password if users changes password for himself */

        if (!Security::canEditUser()) {
            throw new \Exception(L\Access_denied);
        }

        DB\dbQuery('update users_groups set `password` = MD5(CONCAT(\'aero\', $2)), uid = $3 where id = $1', array($user_id, $p['password'], $_SESSION['user']['id'])) or die(DB\dbQueryError());

        return array('success' => true);
    }

    /**
     * Rename user
     */
    public function renameUser($p)
    {
        /* username could be changed by: admin or user owner */
        $name = trim(strtolower(strip_tags($p->name)));
        $matches = preg_match('/^[a-z0-9\._]+$/', $name);
        if (empty($name) || empty($matches)) {
            throw new \Exception(L\Wrong_input_data);
        }

        $user_id = $this->extractId($p->id);

        if (!Security::canEditUser()) {
            throw new \Exception(L\Access_denied);
        }

        DB\dbQuery('update users_groups set `name` = $2, uid = $3 where id = $1', array($user_id, $name, $_SESSION['user']['id'])) or die(DB\dbQueryError());

        return array('success' => true, 'name' => $name);
    }

    /**
     * Rename group
     */
    public function renameGroup($p)
    {
        $title = trim(strip_tags($p->title));
        if (empty($title)) {
            throw new \Exception(L\Wrong_input_data);
        }

        $id = $this->extractId($p->id);

        if (!Security::canEditUser()) {
            throw new \Exception(L\Access_denied);
        }

        DB\dbQuery('update users_groups set `l'.USER_LANGUAGE_INDEX.'` = $2, uid = $3 where id = $1 and type = 1', array($id, $title, $_SESSION['user']['id'])) or die(DB\dbQueryError());

        return array('success' => true, 'title' => $title);
    }

    //---------------------------------------------------------------------------------
    /**
     * Get user preferences
     */
    public static function getUserPreferences($user_id)
    {
        $rez = array();
        $res = DB\dbQuery('select id, name, '.CONFIG\LANGUAGE_FIELDS.', sex, email, language_id, cfg from users_groups where enabled = 1 and did is NULL and id = $1', $user_id) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $r['language'] = $GLOBALS['languages'][$r['language_id']-1];
            $r['cfg'] = empty($r['cfg']) ? array(): json_decode($r['cfg'], true);
            $r['cfg']['long_date_format'] = Util\coalesce($r['cfg']['long_date_format'], $GLOBALS['language_settings'][$r['language']]['long_date_format']);
            $r['cfg']['short_date_format'] = Util\coalesce($r['cfg']['short_date_format'], $GLOBALS['language_settings'][$r['language']]['short_date_format']);
            $rez = $r;
        }
        $res->close();

        return $rez;
    }

    // PRIVATE SECTION
    /**
     * Extract numeric id from a tree node prefixed id
     */
    private function extractId($id)
    {
        if (is_numeric($id)) {
            return $id;
        }
        $a = explode('-', $id);
        $id = array_pop($a);
        if (!is_numeric($id) || ($id < 1)) {
            throw new \Exception(L\Wrong_input_data);
        }

        return $id;
    }
}
