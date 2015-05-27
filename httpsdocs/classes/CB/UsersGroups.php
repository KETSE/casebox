<?php

namespace CB;

class UsersGroups
{
    /**
     * Get the child list to be displayed in user management window in left tree
     */
    public function getChildren($p)
    {
        if (!User::isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        $rez = array();

        if (!Security::canManage()) {
            throw new \Exception(L\get('Access_denied'));
        }
        $path = explode('/', $p['path']);
        $id = array_pop($path);
        $node_type = null;

        if (is_numeric($id)) {
            $res = DB\dbQuery(
                'SELECT type
                FROM users_groups
                WHERE id = $1',
                $id
            ) or die(DB\dbQueryError());

            if ($r = $res->fetch_assoc()) {
                $node_type = $r['type'];
            }
            $res->close();
        }

        // users out of a group
        if ($id == -1) {
            $res = DB\dbQuery(
                'SELECT
                    id
                    ,u.cid
                    ,name
                    ,first_name
                    ,last_name
                    ,sex
                    ,`enabled`
                FROM users_groups u
                LEFT JOIN users_groups_association a ON u.id = a.user_id
                WHERE u.`type` = 2
                    AND u.did IS NULL
                    AND a.group_id IS NULL
                ORDER BY 3, 2'
            ) or die(DB\dbQueryError());

            while ($r = $res->fetch_assoc()) {
                $r['loaded'] = true;
                $rez[] = $r;
            }
            $res->close();

        } elseif (is_null($node_type)) { /* root node childs*/
            $res = DB\dbQuery(
                'SELECT
                    id
                    ,name
                    ,`type`
                    ,`system`
                    ,(SELECT count(*)
                        FROM users_groups_association a
                        JOIN users_groups u ON a.user_id = u.id
                        AND u.did IS NULL
                        WHERE group_id = g.id) `loaded`
                FROM users_groups g
                WHERE `type` = 1
                    AND `system` = 0
                ORDER BY 3, 2'
            ) or die(DB\dbQueryError());

            while ($r = $res->fetch_assoc()) {
                $r['iconCls'] = 'icon-users';
                $r['expanded'] = true;
                $r['loaded'] = empty($r['loaded']);

                $rez[] = $r;
            }
            $res->close();
            $rez[] = array('nid' => -1
                ,'name' => L\get('Users_without_group')
                ,'iconCls' => 'icon-users'
                ,'type' => 1
                ,'expanded' => true
            );
        } else {
            // group users
            $res = DB\dbQuery(
                'SELECT
                    u.id
                    ,u.cid
                    ,u.name
                    ,first_name
                    ,last_name
                    ,sex
                    ,enabled
                FROM users_groups_association a
                JOIN users_groups u ON a.user_id = u.id
                WHERE a.group_id = $1
                    AND u.did IS NULL
                ORDER BY 4, 5, 3',
                $id
            ) or die(DB\dbQueryError());

            while ($r = $res->fetch_assoc()) {
                $r['loaded'] = true;
                $rez[] = $r;
            }
            $res->close();
        }

        $pid = empty($id) ? 'is null' : ' = '.intval($id);

        /* collapse first and last names into title */
        for ($i=0; $i < sizeof($rez); $i++) {
            $rez[$i]['title'] = User::getDisplayName($rez[$i]);

            unset($rez[$i]['first_name']);
            unset($rez[$i]['last_name']);

            if (isset($rez[$i]['id'])) {
                $rez[$i]['nid'] = $rez[$i]['id'];
                unset($rez[$i]['id']);
            }
        }
        /* end of collapse first and last names into title */

        return $rez;
    }

    /**
     * Associating a user to a group
     */
    public function associate($user_id, $group_id)
    {
        if (!User::isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        if (!Security::canManage()) {
            throw new \Exception(L\get('Access_denied'));
        }
        $res = DB\dbQuery(
            'SELECT user_id
            FROM users_groups_association
            WHERE user_id = $1
                AND group_id = $2',
            array($user_id, $group_id)
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            throw new \Exception(L\get('UserAlreadyInOffice'));
        }
        $res->close();
        DB\dbQuery(
            'INSERT INTO users_groups_association (user_id, group_id, cid)
            VALUES ($1, $2, $3)',
            array(
                $user_id
                ,$group_id
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        Security::calculateUpdatedSecuritySets();

        Solr\Client::runBackgroundCron();

        return array('success' => true);
    }

    /**
     * Deassociating a user from a group
     */
    public function deassociate($user_id, $group_id)
    {
        if (!User::isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        if (!Security::canManage()) {
            throw new \Exception(L\get('Access_denied'));
        }
        $res = DB\dbQuery(
            'DELETE
            FROM users_groups_association
            WHERE user_id = $1
                AND group_id = $2',
            array($user_id, $group_id)
        ) or die(DB\dbQueryError());

        Security::calculateUpdatedSecuritySets();

        Solr\Client::runBackgroundCron();

        //return if the user is associated to another office,
        //otherwise it shoul be added to Users out of office folder
        $outOfGroup = true;
        $res = DB\dbQuery(
            'SELECT group_id
            FROM users_groups_association
            WHERE user_id = $1 LIMIT 1',
            $user_id
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $outOfGroup = false;
        }

        return array('success' => true, 'outOfGroup' => $outOfGroup);
    }

    /**
     * Add a new user
     * params: name, group_id
     */
    public function addUser($p)
    {
        if (!User::isVerified()) {
            return array(
                'success' => false
                ,'verify' => true
            );
        }

        if (!Security::canManage()) {
            throw new \Exception(L\get('Access_denied'));
        }

        $rez = array(
            'success' => false
            ,'msg' => L\get('Missing_required_fields')
        );

        $p['name'] = strip_tags($p['name']);
        $p['name'] = trim($p['name']);

        if (empty($p['name'])) {
            return $rez;
        }

        // validate input params
        if (!preg_match('/^[a-z\.0-9_]+$/i', $p['name'])) {
            return array(
                'success' => false
                ,'msg' => 'Invalid username. Use only letters, digits, "dot" and/or "underscore".'
            );
        }

        $p['first_name'] = Purify::humanName($p['first_name']);
        $p['last_name'] = Purify::humanName($p['last_name']);

        if (!empty($p['email'])) {
            if (!filter_var(
                $p['email'],
                FILTER_VALIDATE_EMAIL
            )) {
                return array(
                    'success' => false
                    ,'msg' => L\get('InvalidEmail')
                );
            }
        }

        //check if user with such email doesn exist
        $user_id = User::getIdByEmail($p['email']);
        if (!empty($user_id)) {
            throw new \Exception(L\get('UserEmailExists'));
        }

        $user_id = 0;
        /*check user existance, if user already exists but is deleted
        then its record will be used for new user */
        $res = DB\dbQuery(
            'SELECT id
            FROM users_groups
            WHERE name = $1
                AND did IS NULL',
            $p['name']
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            throw new \Exception(L\get('User_exists'));
        }
        $res->close();
        /*end of check user existance */

        DB\dbQuery(
            'INSERT INTO users_groups (
                `name`
                ,first_name
                ,last_name
                ,`cid`
                ,language_id
                ,cdate
                ,uid
                ,email)
            VALUES($1
                ,$2
                ,$3
                ,$4
                ,$5
                ,CURRENT_TIMESTAMP
                ,$4
                ,$6)
            ON DUPLICATE KEY
            UPDATE id = last_insert_id(id)
                ,`name` = $1
                ,`first_name` = $2
                ,`last_name` = $3
                ,`cid` = $4
                ,last_login = NULL
                ,login_successful = NULL
                ,login_from_ip = NULL
                ,last_logout = NULL
                ,last_action_time = NULL
                ,enabled = 1
                ,cdate = CURRENT_TIMESTAMP
                ,did = NULL
                ,ddate = NULL
                ,`password` = NULL
                ,`password_change` = NULL
                ,`recover_hash` = NULL
                ,language_id = $5
                ,`cfg` = NULL
                ,`data` = NULL
                ,email = $6
                ,uid = $4
                ,cdate = CURRENT_TIMESTAMP',
            array(
                $p['name']
                ,$p['first_name']
                ,$p['last_name']
                ,$_SESSION['user']['id']
                ,Config::get('language_index')
                ,$p['email']
            )
        ) or die(DB\dbQueryError());
        if ($user_id = DB\dbLastInsertId()) {
            $rez = array(
                'success' => true
                ,'data' => array('id' => $user_id)
            );
            $p['id'] = $user_id;
        }

        /* in case it was a deleted user we delete all old acceses */
        DB\dbQuery('DELETE FROM users_groups_association WHERE user_id = $1', $user_id) or die(DB\dbQueryError());
        DB\dbQuery('DELETE FROM tree_acl WHERE user_group_id = $1', $rez['data']['id']) or die(DB\dbQueryError());
        /* end of in case it was a deleted user we delete all old acceses */

        // associating user to group if group was specified
        if (isset($p['group_id']) && is_numeric($p['group_id'])) {
            DB\dbQuery(
                'INSERT INTO users_groups_association (user_id, group_id, cid)
                VALUES($1, $2, $3)
                ON duplicate KEY
                UPDATE cid = $3',
                array(
                    $user_id
                    ,$p['group_id']
                    ,$_SESSION['user']['id']
                )
            ) or die(DB\dbQueryError());
            $rez['data']['group_id'] = $p['group_id'];
        } else {
            $rez['data']['group_id'] = 0;
        }

        //check if send invite is set and create notification
        if (!empty($p['send_invite'])) {
            $this->sendResetPasswordMail($user_id, 'invite');
        }

        Security::calculateUpdatedSecuritySets();

        Solr\Client::runBackgroundCron();

        return $rez;
    }

    /**
     * Delete a user from user management window
     */
    public function deleteUser($user_id)
    {
        if (!User::isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        if (!Security::canManage()) {
            throw new \Exception(L\get('Access_denied'));
        }
        $res = DB\dbQuery(
            'UPDATE users_groups
            SET did = $2
               ,ddate = CURRENT_TIMESTAMP
            WHERE id = $1',
            array(
                $user_id
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        //TODO: destroy user session if loged in
        return array(
            'success' => DB\dbAffectedRows() ? true : false,
            'data' => array($user_id, $_SESSION['user']['id'])
        );
    }

    /**
     * Delete a group from user management window
     */
    public function deleteGroup($group_id)
    {
        if (!Security::canEditUser($group_id)) {
            throw new \Exception(L\get('Access_denied'));
        }

        /* Delete group record. All security rules with this group wil be deleted by foreign key.
        On deleting a group also the users associations are deleted by the foreign key
        and corresponding security sets are marked, by trigger, as updated.
        */
        DB\dbQuery('DELETE FROM users_groups WHERE id = $1 AND `type` = 1', $group_id) or die(DB\dbQueryError());
        /* call the recalculation method for security sets. */
        Security::calculateUpdatedSecuritySets();

        Solr\Client::runBackgroundCron();

        return array('success' => true);
    }

    /**
     * Retreive user details data to be displayed in user details window
     */
    public function getUserData($p)
    {
        if (!User::isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        if (($_SESSION['user']['id'] != $p['data']['id']) && !Security::canManage()) {
            throw new \Exception(L\get('Access_denied'));
        }
        $user_id = $p['data']['id'];
        $rez = array('success' => false, 'msg' => L\get('Wrong_id'));

        $res = DB\dbQuery(
            'SELECT id
                ,cid
                ,name
                ,first_name
                ,last_name
                ,sex
                ,email
                ,enabled
                ,data
                ,last_action_time
                ,cdate
                ,cid
            FROM users_groups u
            WHERE id = $1',
            $user_id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $r['title'] = User::getDisplayName($r);
            $r['data'] = Util\toJSONArray($r['data']);
            $r['last_action_time'] = Util\formatMysqlTime($r['last_action_time']);
            $r['cdate'] = Util\formatMysqlTime($r['cdate']);
            $r['owner'] = User::getDisplayName($r['cid']);

            $rez = array('success' => true, 'data' => $r);
        }
        $res->close();
        if ($rez['success'] == false) {
            throw new \Exception(L\get('Wrong_id'));
        }

        $rez['data']['template_id'] = User::getTemplateId();

        return $rez;
    }

    /**
     * Get access data for a user to be displayed in user management window
     */
    public function getAccessData($user_id = false)
    {
        if (!User::isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        if (!Security::canManage()) {
            throw new \Exception(L\get('Access_denied'));
        }
        $user_id = $this->extractId($user_id);
        $rez = $this->getUserData(array( 'data' => array('id' => $user_id)));

        $rez['data']['groups'] = array();
        $res = DB\dbQuery(
            'SELECT a.group_id
            FROM users_groups_association a
            WHERE user_id = $1',
            $user_id
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $rez['data']['groups'][] = $r['group_id'];
        }
        $res->close();

        //set tsv status
        $tsv = User::getTSVConfig($user_id);
        $rez['data']['tsv'] = empty($tsv['method'])
            ? 'none'
            : L\get('TSV_' . $tsv['method']);

        return $rez;
    }

    /**
     * Save access data specified for a user in UserManagement form (groups association)
     *
     *
     */
    public function saveAccessData($p)
    {
        if (!User::isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        if (!Security::canManage()) {
            throw new \Exception(L\get('Access_denied'));
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
                VALUES($1, $2, $3)
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

        Security::calculateUpdatedSecuritySets($user_id);

        Solr\Client::runBackgroundCron();

        return array('success' => true);
    }

    /**
     * Get an array of group ids for specified user.
     * If no user is passed then current loged user is analized.
     *
     */
    public static function getGroupIdsForUser($user_id = false)
    {
        if ($user_id === false) {
            $user_id = $_SESSION['user']['id'];
        }

        $groups = array();
        $res = DB\dbQuery(
            'SELECT group_id
            FROM users_groups_association
            WHERE user_id = $1',
            $user_id
        ) or die( DB\dbQueryError() );

        while ($r = $res->fetch_assoc()) {
            $groups[] = $r['group_id'];
        }
        $res->close();

        return $groups;
    }

    /**
     * Change user password.
     */
    public function changePassword($p)
    {
        if (!User::isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        /* passord could be changed by: admin, user owner, user himself */
        if (empty($p['password']) || ($p['password'] != $p['confirmpassword'])) {
            throw new \Exception(L\get('Wrong_input_data'));
        }
        $user_id = $this->extractId($p['id']);

        /* check for old password if users changes password for himself */
        if ($_SESSION['user']['id'] == $user_id) {
            $res = DB\dbQuery(
                'SELECT id
                FROM users_groups
                WHERE id = $1
                    AND `password` = MD5(CONCAT(\'aero\', $2))',
                array(
                    $user_id
                    ,$p['currentpassword']
                )
            ) or die(DB\dbQueryError());
            if (!$res->fetch_assoc()) {
                throw new \Exception(L\get('WrongCurrentPassword'));
            }
            $res->close();
        }
        /* end of check for old password if users changes password for himself */

        if (!Security::canEditUser($user_id)) {
            throw new \Exception(L\get('Access_denied'));
        }

        DB\dbQuery(
            'UPDATE users_groups
            SET `password` = MD5(CONCAT(\'aero\', $2))
                ,uid = $3
            WHERE id = $1',
            array(
                $user_id
                ,$p['password']
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        Session::clearUserSessions($user_id);

        return array('success' => true);
    }

    /**
     * send recovery password email for given user id
     * so that the user can set new password and enter the system
     * @param  int     $userId
     * @return boolean
     */
    public static function sendResetPasswordMail($userId, $template = 'recover')
    {
        if (!is_numeric($userId) ||
            (User::isLoged() && !Security::canEditUser($userId))
        ) {
            return false;
        }

        $mail = '';
        $subject = '';

        switch ($template) {
            case 'invite':
                $mail = System::getEmailTemplate('email_invite');
                $subject = L\get('MailInviteSubject');

                break;
            case 'recover':
                $mail = System::getEmailTemplate('password_recovery_email');
                $subject = L\get('MailRecoverSubject');

                break;

            default:
                return false;
        }

        if (empty($mail)) {
            return false;
        }

        $userData = User::getPreferences($userId);
        $userEmail = User::getEmail($userData);

        if (empty($userEmail)) {
            return false;
        }

        /* generating invite hash and sending mail */
        $hash = User::generateRecoveryHash(
            $userId,
            $userId . $userEmail . date(DATE_ISO8601)
        );

        $href = Util\getCoreHost().'recover/reset-password/?h='.$hash;

        /* replacing placeholders in template and subject */
        $replacements  = array(
            '{projectTitle}' => Config::getProjectName()
            ,'{fullName}' => User::getDisplayName($userData)
            ,'{username}' => User::getUsername($userData)
            ,'{userEmail}' => $userEmail
            ,'{creatorFullName}' => User::getDisplayName()
            ,'{creatorUsername}' => User::getUsername()
            ,'{creatorEmail}' => User::getEmail()
            ,'{href}' => $href
            ,'{link}' => '<a href="'.$href.'" >'.$href.'</a>'
        );

        $search = array_keys($replacements);
        $replace = array_values($replacements);

        $mail = str_replace($search, $replace, $mail);
        $subject = str_replace($search, $replace, $subject);

        return @System::sendMail(
            $userEmail,
            $subject,
            $mail
        );
    }

    /**
     * shortcut to previous function to return json responce
     * @param  int   $userId
     * @return array
     */
    public function sendResetPassMail($userId)
    {
        return array(
            'success' => $this->sendResetPasswordMail($userId)
        );
    }

    public function disableTSV($userId)
    {
        if (!User::isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        if (is_nan($userId)) {
            throw new \Exception(L\get('Wrong_input_data'));
        }

        if (!Security::canEditUser($userId)) {
            throw new \Exception(L\get('Access_denied'));
        }

        return User::disableTSV($userId);
    }

    /**
     * Rename user
     */
    public function renameUser($p)
    {
        if (!User::isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        /* username could be changed by: admin or user owner */
        $name = trim(strtolower(strip_tags($p['name'])));
        $matches = preg_match('/^[a-z0-9\._]+$/i', $name);

        if (empty($name) || empty($matches)) {
            throw new \Exception(L\get('Wrong_input_data'));
        }

        $user_id = $this->extractId($p['id']);

        if (!Security::canEditUser($user_id)) {
            throw new \Exception(L\get('Access_denied'));
        }

        DB\dbQuery(
            'UPDATE users_groups
            SET `name` = $2
                , uid = $3
            WHERE id = $1',
            array(
                $user_id
                ,$name
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        return array('success' => true, 'name' => $name);
    }

    /**
     * Set user enabled or disabled
     */
    public function setUserEnabled($p)
    {
        if (!User::isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        $userId = $this->extractId($p['id']);
        $enabled = !empty($p['enabled']);

        if (!Security::canEditUser($userId)) {
            throw new \Exception(L\get('Access_denied'));
        }

        User::setEnabled($userId, $enabled);

        return array('success' => true, 'enabled' => $enabled);
    }

    /**
     * Rename group
     */
    public function renameGroup($p)
    {
        if (!User::isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        $title = Purify::humanName($p['title']);

        if (empty($title)) {
            throw new \Exception(L\get('Wrong_input_data'));
        }

        $id = $this->extractId($p['id']);

        if (!Security::canEditUser($id)) {
            throw new \Exception(L\get('Access_denied'));
        }

        DB\dbQuery(
            'UPDATE users_groups
            SET name = $2, uid = $3
            WHERE id = $1 AND type = 1',
            array(
                $id
                ,$title
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        return array('success' => true, 'title' => $title);
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
            throw new \Exception(L\get('Wrong_input_data'));
        }

        return $id;
    }
}
