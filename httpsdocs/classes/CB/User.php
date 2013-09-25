<?php
namespace CB;

class User
{
    /**
     * login method for user authentication
     * @param  varchar $login username
     * @param  varchar $pass  password
     * @return array   json responce
     */
    public static function login($login, $pass)
    {
        $ips = '|'.Util\getIPs().'|';

        session_regenerate_id(false);
        $_SESSION['ips'] = $ips;
        $_SESSION['key'] = md5($ips.$login.$pass.time());
        $_COOKIE['key'] = $_SESSION['key'];
        setcookie('key', $_SESSION['key'], 0, '/', $_SERVER['SERVER_NAME'], !empty($_SERVER['HTTPS']), true);

        $rez = array('success' => false);
        $user_id = false;

        /* try to authentificate */
        $res = DB\dbQuery('CALL p_user_login($1, $2, $3)', array($login, $pass, $ips)) or die( DB\dbQueryError() );
        if (($r = $res->fetch_row()) && ($r[1] == 1)) {
            $user_id = $r[0];
        }
        $res->close();
        DB\dbCleanConnection();

        if ($user_id) {
            $rez = array('success' => true, 'user' => array());

            $sql = 'SELECT
                    u.id
                    ,u.`language_id`
                    ,first_name
                    ,last_name
                    ,email
                    ,sex
                    ,cfg
                FROM users_groups u
                WHERE u.id = $1';

            $res = DB\dbQuery($sql, $user_id) or die( DB\dbQueryError() );
            if ($r = $res->fetch_assoc()) {
                $r['admin'] = Security::isAdmin($user_id);
                $r['manage'] = Security::canManage($user_id);

                $r['language'] = $GLOBALS['languages'][$r['language_id']-1];
                $r['locale'] =  $GLOBALS['language_settings'][$r['language']]['locale'];

                $r['cfg'] = json_decode($r['cfg'], true) or array();
                // do not expose security params
                unset($r['cfg']['security']);

                if (empty($r['cfg']['long_date_format'])) {
                    $r['cfg']['long_date_format'] = $GLOBALS['language_settings'][$r['language']]['long_date_format'];
                }
                if (empty($r['cfg']['short_date_format'])) {
                    $r['cfg']['short_date_format'] = $GLOBALS['language_settings'][$r['language']]['short_date_format'];
                }
                $r['cfg']['time_format'] = $GLOBALS['language_settings'][$r['language']]['time_format'];

                $rez['user'] = $r;
                $_SESSION['user'] = $r;
                setcookie('L', $r['language']);

                /* get user groups */
                $rez['user']['groups'] = UsersGroups::getGroupIdsForUser();
                $_SESSION['user']['groups'] = $rez['user']['groups'];
                /* end of get user groups */
            }
            $res->close();

        } else {
            $rez['msg'] = L\Auth_fail;
        }
        Log::add(
            array(
                'action_type' => 1
                ,'result' => isset($_SESSION['user'])
                ,'info' => 'user: '.$login."\nip: ".$ips
            )
        );

        return $rez;
    }
    /**
     * password verification method used for accessing sensitive data (like profile form)
     * or for additional identity check
     * @param varchar $passwd
     * return array json responce
     */
    public static function verifyPassword($pass)
    {
        $rez = array( 'success' => false );
        unset($_SESSION['verified']);
        $res = DB\dbQuery(
            'SELECT id
            FROM users_groups
            WHERE id = $1
                AND `password`= md5($2)',
            array(
                $_SESSION['user']['id']
                ,'aero'.$pass
            )
        ) or die( DB\dbQueryError() );

        if ($r = $res->fetch_row()) {
            $rez['success'] = true;
            $_SESSION['verified'] = time();
        } else {
            $rez['msg'] = L\Auth_fail;
        }
        $res->close();

        return $rez;
    }

    /**
     * email verification method. send a confirmation message
     * to specified mail with a url containing secret key
     * @param varchar $email
     * return array json responce
     */
    public static function verifyEmail($email)
    {
    }

    /**
     * Phone verification method. Send an sms message and prompt to insert received code
     * @param varchar $phone
     * return array json responce
     */
    public function verifyPhone($p)
    {
        $rez = array( 'success' => true );
        $phone = preg_replace('/[^0-9]+/', '', $p->country_code.$p->phone_number);

        $rez['info'] = \FreeSMSGateway::sendSms(
            array(
                'phone' => $phone
                ,'message' => $this->getGACode()
            )
        );

        return $rez;
    }

    /**
     * enable Two Step Verification mechanism
     * @param  object $p
     * @return json   response
     */
    public function enableTSV($p)
    {
        // validate TSV mechanism
        if (!in_array($p->method, array('ga', 'sms', 'ybk'))) {
            return array('success' => false, 'msg' => 'Invalid authentication mechanism');
        }
        $data = empty($p->data) ? array(): (array) $p->data;
        if (!empty($_SESSION['lastTSV'][$p->method])) {
            //return array('success' => false, 'msg' => 'Error enabling TSV.');
            $data = array_merge($_SESSION['lastTSV'][$p->method], $data);
        }

        $rez = array( 'success' => true );

        $authenticator = $this->getTSVAuthenticator($p->method);
        $data = $authenticator->createSecretData($data);
        $authenticator->setSecretData($data);

        if ($authenticator->verifyCode($data['code'])) {
            $cfg = array(
                'method' => $p->method
                ,'sd' => $data
            );
            $this->setTSVConfig($cfg);
            unset($_SESSION['lastTSV']);
        } else {
            $rez['success'] = false;
        }

        return $rez;
    }

    public function disableTSV()
    {
        $this->setTSVConfig(null);

        return array('success' => true);
    }

    /**
     * check if user is loged in current session
     */
    public static function isLoged()
    {
        return ( !empty($_COOKIE['key']) &&
            !empty($_SESSION['key']) &&
            !empty($_SESSION['ips']) &&
            !empty($_SESSION['user']) &&
            ($_COOKIE['key'] == $_SESSION['key']) &&
            ('|'.Util\getIPs().'|' == $_SESSION['ips']) &&
            !empty($_SESSION['user']['TSV_checked'])
            );
    }

    /**
     * check if user did a password verification check in specified period of time. Default is 5 minutes
     */
    public static function isVerified($seconds = 300)
    {
        return ( !empty($_SESSION['verified']) &&
            ( (time() - $_SESSION['verified']) < $seconds )
            );
    }

    /**
     * get login info for current loged user
     * @return array json responce
     */
    public function getLoginInfo()
    {
        Browser::checkRootFolder();
        User::checkUserFolders();

        @$rez = array(
            'success' => true
            ,'config' => array(
                'task_categories' => constant('CB\\CONFIG\\TASK_CATEGORIES')
                ,'responsible_party' => constant('CB\\CONFIG\\RESPONSIBLE_PARTY')
                ,'responsible_party_default' => constant('CB\\CONFIG\\RESPONSIBLE_PARTY_DEFAULT')
                ,'folder_templates' => $GLOBALS['folder_templates']
                ,'default_task_template' => constant('CB\\CONFIG\\DEFAULT_TASK_TEMPLATE')
                ,'default_event_template' => constant('CB\\CONFIG\\DEFAULT_EVENT_TEMPLATE')
            )
            ,'user' => $_SESSION['user']
        );
        $rez['user']['cfg']['short_date_format'] = str_replace('%', '', $rez['user']['cfg']['short_date_format']);
        $rez['user']['cfg']['long_date_format'] = str_replace('%', '', $rez['user']['cfg']['long_date_format']);
        $rez['user']['cfg']['time_format'] = str_replace('%', '', $rez['user']['cfg']['time_format']);

        return $rez;
    }

    /**
     * get account data for profile and security forms
     */
    public function getAccountData()
    {
        if (!$this->isVerified()) {
            return array('success' => false, 'verify' => true);
        }
        $_SESSION['verified'] = time(); //update verification time

        return array(
            'success' => true
            ,'profile' => $this->getProfileData($_SESSION['user']['id'])
            ,'security' => $this->getSecurityData()
        );
    }

    /**
     * get profile data for a user. This function receives user_id as param because user profile data can be edited by another user (owner).
     */
    public function getProfileData($user_id)
    {
        if (!Security::canEditUser($user_id)) {
            throw new \Exception(L\Access_denied);
        }

        $rez = array();
        $res = DB\dbQuery(
            'SELECT id
                 , name
                 , first_name
                 , last_name
                 , sex
                 , email
                 , language_id
                 , cfg
            FROM users_groups
            WHERE enabled = 1
                AND did IS NULL
                AND id = $1',
            $user_id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $cfg = empty($r['cfg']) ? array(): json_decode($r['cfg'], true);
            unset($r['cfg']);

            $r['language'] = $GLOBALS['languages'][$r['language_id']-1];

            $r['long_date_format'] = empty($cfg['long_date_format']) ?
                $GLOBALS['language_settings'][$r['language']]['long_date_format'] :
                $cfg['long_date_format'];

            $r['short_date_format'] = empty($cfg['short_date_format']) ?
                $GLOBALS['language_settings'][$r['language']]['short_date_format'] :
                $cfg['short_date_format'];

            if (!empty($cfg['country_code'])) {
                $r['country_code'] = $cfg['country_code'];
            }
            if (!empty($cfg['phone'])) {
                $r['phone'] = $cfg['phone'];
            }
            if (!empty($cfg['timezone'])) {
                $r['timezone'] = $cfg['timezone'];
            }
            $r['template_id'] = User::getTemplateId();
            VerticalEditGrid::getData('users_groups', $r);
            $rez = $r;
        }
        $res->close();

        $rez['success'] = true;

        return $rez;
    }

    private function getSecurityData()
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT password_change
                 , cfg
            FROM users_groups
            WHERE enabled = 1
                AND did IS NULL
                AND id = $1',
            $_SESSION['user']['id']
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $cfg = empty($r['cfg']) ? array(): json_decode($r['cfg'], true);
            if (!empty($cfg['security'])) {
                $rez = $cfg['security'];
            }
            $rez['password_change'] = $r['password_change'];
            if (empty($rez['phone']) && !empty($cfg['phone'])) {
                $rez['phone'] = $cfg['phone'];
            }
        }
        $res->close();

        return $rez;
    }

    /**
     * save user profile form data
     */
    public function saveProfileData($data)
    {
        if (!Security::canEditUser($data->id)) {
            throw new \Exception(L\Access_denied);
        }

        $rez = array();
        $cfg = $this->getUserConfig();

        if (isset($data->country_code)) {
            $cfg['country_code'] = $data->country_code;
        }
        if (isset($data->phone)) {
            $cfg['phone'] = $data->phone;
        }
        if (isset($data->timezone)) {
            $cfg['timezone'] = $data->timezone;
        }
        if (isset($data->short_date_format)) {
            $cfg['short_date_format'] = $data->short_date_format;
        }
        if (isset($data->long_date_format)) {
            $cfg['long_date_format'] = $data->long_date_format;
        }

        @DB\dbQuery(
            'UPDATE users_groups
            SET first_name = $2
                , last_name = $3
                , sex = $4
                , email = $5
                , language_id = $6
                , cfg = $7
            WHERE id = $1',
            array(
                $data->id
                ,$data->first_name
                ,$data->last_name
                ,$data->sex
                ,$data->email
                ,$data->language_id
                ,json_encode($cfg)
            )
        ) or die( DB\dbQueryError() );

        VerticalEditGrid::saveData('users_groups', $data);

        return array('success' => true);
    }

    public function saveSecurityData($data)
    {
        if (!$this->isVerified()) {
            return array('success' => false, 'verify' => true);
        }
        $_SESSION['verified'] = time(); //update verification time
        $rez = array();
        $cfg = $this->getUserConfig();

        if (empty($cfg['security'])) {
            $cfg['security'] = array();
        }
        if (empty($data->recovery_mobile)) {
            unset($cfg['security']['recovery_mobile']);
        } else {
            $cfg['security']['recovery_mobile'] = true;
        }
        if (empty($data->country_code)) {
            unset($cfg['security']['country_code']);
        } else {
            $cfg['security']['country_code'] = $data->country_code;
        }
        if (empty($data->phone_number)) {
            unset($cfg['security']['phone_number']);
        } else {
            $cfg['security']['phone_number'] = $data->phone_number;
        }

        if (empty($data->recovery_email)) {
            unset($cfg['security']['recovery_email']);
        } else {
            $cfg['security']['recovery_email'] = true;
        }
        if (empty($data->email)) {
            unset($cfg['security']['email']);
        } else {
            $cfg['security']['email'] = $data->email;
        }

        if (empty($data->recovery_question)) {
            unset($cfg['security']['recovery_question']);
        } else {
            $cfg['security']['recovery_question'] = true;
        }
        if (empty($data->question_idx)) {
            unset($cfg['security']['question_idx']);
        } else {
            $cfg['security']['question_idx'] = $data->question_idx;
        }
        if (empty($data->answer)) {
            unset($cfg['security']['answer']);
        } else {
            $cfg['security']['answer'] = $data->answer;
        }

        $this->setUserConfig($cfg);

        return array('success' => true);
    }

    /**
     * get secret data
     * @param  varchar $p authentication mechanism abreviation ('ga', 'sms', 'ybk')
     * @return json    response
     */
    public function getTSVTemplateData($p)
    {
        // validate TSV mechanism
        if (!in_array($p, array('ga', 'sms', 'ybk'))) {
            return array('success' => false, 'msg' => 'Invalid authentication mechanism');
        }

        $rez = array(
            'success' => true
            ,'data' => null
        );

        $cfg = $this->getTSVConfig();

        $authenticator = $this->getTSVAuthenticator($p);

        if (empty($cfg['method'])
            || empty($cfg['sd'])
            || ($cfg['method'] != $p)
        ) {
            $_SESSION['lastTSV'][$p] = $authenticator->prepareSecretDataCreation();
        } else {
            $_SESSION['lastTSV'][$p] = $cfg['sd'];
        }
        $authenticator->setSecretData($_SESSION['lastTSV'][$p]);
        $rez['data'] = $authenticator->getTemplateData();

        return $rez;
    }

    /* get code for Google Authenticator */
    private function getGACode()
    {
        $sk = $this->getGASk();
        $sk = $sk['sk'];
        $ga = new \GoogleAuthenticator();

        return $ga->getCode($sk);
    }

    /* verify given Google Authenticator code */
    public function verifyGACode($code)
    {
        $sk = $this->getGASk();
        $sk = $sk['sk'];
        $ga = new \GoogleAuthenticator();

        return $ga->verifyCode($sk, $code);
    }

    /**
     * logout current loged user
     * @return array json responce
     */
    public function logout()
    {
        $rez = array('success' => true);
        Log::add(array('action_type' => 2, 'result' => 1));

        while (!empty($_SESSION['last_sessions'])) {
            @unlink(session_save_path().DIRECTORY_SEPARATOR.'sess_'.array_shift($_SESSION['last_sessions']));
        }
        session_destroy();

        return $rez;
    }

    /**
     * change language for currently loged user
     * @param  int   $id language id
     * @return array json responce
     */
    public function setLanguage($id)
    {
        if (isset($GLOBALS['languages'][$id -1])) {
            $_SESSION['user']['language_id'] = $id;
            $_SESSION['user']['language'] = $GLOBALS['languages'][$id -1];
            setcookie('L', $GLOBALS['languages'][$id -1]);
        } else {
            return array('success' => false);
        }
        DB\dbQuery('UPDATE users_groups SET language_id = $2 WHERE id = $1', array($_SESSION['user']['id'], $id)) or die( DB\dbQueryError() );

        return array('success' => true);
    }

    /**
     * checkUserFolders
     * @param  boolean $user_id
     * @return boolean
     */
    public static function checkUserFolders($user_id = false)
    {
        $result = true;
        if (!is_numeric($user_id)) {
            $user_id = $_SESSION['user']['id'];
        }

        $affected_rows = 0;

        /* check user home folder existace */
        $home_folder_id = null;

        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE (user_id = $1)
                    AND (`system` = 1)
                    AND (`type` = 1)
                    AND (`subtype` = 2)
                    AND (pid IS NULL)',
            $user_id
        ) or die( DB\dbQueryError() );

        if ($r = $res->fetch_row()) {
            $home_folder_id = $r[0];
        }
        $res->close();
        if (is_null($home_folder_id)) {
            $cfg = defined('CB\\CONFIG\\DEFAULT_HOME_FOLDER_CFG') ? CONFIG\DEFAULT_HOME_FOLDER_CFG : null;

            DB\dbQuery(
                'INSERT INTO tree (name, user_id, `system`, `type`, `subtype`, cfg, template_id)
                VALUES(\'[Home]\', $1
                                 , 1
                                 , 1
                                 , 2
                                 , $2
                                 , $3
                       )',
                array($user_id
                    ,$cfg
                    ,CONFIG\DEFAULT_FOLDER_TEMPLATE
                )
            ) or die( DB\dbQueryError() );

            $home_folder_id = DB\dbLastInsertId();
            $affected_rows++;

            /* insert home folder security record in tree_acl */
            DB\dbQuery(
                'INSERT INTO tree_acl (node_id, user_group_id, allow, deny)
                VALUES ($1
                      , $2
                      , 4095
                      , 0) ON duplicate KEY
                UPDATE allow = 4095
                     , deny = 0',
                array($home_folder_id
                    ,$user_id
                )
            ) or die( DB\dbQueryError() );

            $affected_rows += DB\dbAffectedRows();
        }

        /* check users "My documents" folder existace */
        $my_docs_id = null;
        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE (user_id = $1)
                    AND (`system` = 1)
                    AND (`type` = 1)
                    AND (`subtype` = 3)
                    AND (pid = $2)',
            array($user_id
                , $home_folder_id
            )
        ) or die( DB\dbQueryError() );

        if ($r = $res->fetch_row()) {
            $my_docs_id = $r[0];
        }
        $res->close();
        if (is_null($my_docs_id)) {
            DB\dbQuery(
                'INSERT INTO tree (pid, name, user_id, `system`, `type`, `subtype`, template_id)
                VALUES($1, \'[MyDocuments]\', $2
                                            , 1
                                            , 1
                                            , 3
                                            , $3)',
                array($home_folder_id
                    ,$user_id
                    ,CONFIG\DEFAULT_FOLDER_TEMPLATE
                )
            ) or die( DB\dbQueryError() );

            $my_docs_id = DB\dbLastInsertId();
            $affected_rows++;
        }

        if ($affected_rows > 0) {
            Solr\Client::runCron();
        }

        return true;
    }

    /**
     * get home folder id for specified user id. If folder does not exist it is created automaticly.
     * @param  int $user_id
     * @return int home folder id
     */
    public static function getUserHomeFolderId($user_id = false)
    {
        $rez = null;
        if ($user_id == false) {
            $user_id = $_SESSION['user']['id'];
        }

        if (defined('CB\\HOME_FOLDER'.$user_id)) {
            return constant('CB\\HOME_FOLDER'.$user_id);
        }

        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE user_id = $1
                    AND SYSTEM = 1
                    AND (pid IS NULL)
                    AND TYPE = 1
                    AND subtype = 2',
            $_SESSION['user']['id']
        ) or die( DB\dbQueryError() );

        if ($r = $res->fetch_row()) {
            $rez = $r[0];
        }
        $res->close();
        define('CB\\HOME_FOLDER'.$user_id, $rez);

        return $rez;
    }

    /**
     * get email folder id for specified user id. If folder does not exist it is created automaticly.
     * @param  int $user_id
     * @return int email folder id
     */
    public static function getEmailFolderId($user_id = false)
    {
        $rez = null;
        if (empty($user_id)) {
            $user_id = $_SESSION['user']['id'];
        }
        $pid = User::getUserHomeFolderId($user_id);

        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE user_id = $1
                AND SYSTEM = 1
                AND pid =$2
                AND TYPE = 1
                AND subtype = 6',
            array(
                $_SESSION['user']['id']
                ,$pid
            )
        ) or die( DB\dbQueryError() );

        if ($r = $res->fetch_row()) {
            $rez = $r[0];
        }
        $res->close();
        if (empty($rez)) {
            DB\dbQuery(
                'INSERT INTO tree (pid, user_id, `system`, `type`, `subtype`, `name`, cid, uid, template_id)
                VALUES (
                    $1
                    ,$2
                    ,1
                    ,1
                    ,6
                    ,\'[Emails]\'
                    ,$3
                    ,$3
                    ,$4)',
                array(
                    $pid
                    ,$user_id
                    ,$_SESSION['user']['id']
                    ,CONFIG\DEFAULT_FOLDER_TEMPLATE
                )
            ) or die( DB\dbQueryError() );
            $rez = DB\dbLastInsertId();
            Solr\Client::runCron();
        }

        return $rez;
    }

    /**
     * get left accordion and top toolbar items
     * @return array json reponce
     */
    public function getMainMenuItems()
    {
        $userMenu = new UserMenu();
        $rez = array(
            'success' => true
            ,'items' => $userMenu->getAccordionItems()
            ,'tbarItems' => $userMenu->getToolbarItems()
        );

        return $rez;
    }

    /**
     * upload user photo
     * @param  array $p upload params using form post
     * @return array json responce
     */
    public function uploadPhoto($p)
    {
        if (!is_numeric($p['id'])) {
            return array('success' => false, 'msg' => L\Wrong_id);
        }
        $f = &$_FILES['photo'];
        if (!in_array($f['error'], array(UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE))) {
            return array('success' => false, 'msg' => L\Error_uploading_file .': '.$f['error']);
        }
        if (substr($f['type'], 0, 6) !== 'image/') {
            return array('success' => false, 'msg' => 'Not an image');
        }

        $photoName = $p['id'].'_'.$object_title = preg_replace('/[^a-z0-9\.]/i', '_', $f['name']);

        if (!file_exists(PHOTOS_PATH)) {
            @mkdir(PHOTOS_PATH, 0755, true);
        }

        move_uploaded_file($f['tmp_name'], PHOTOS_PATH.$photoName);

        $res = DB\dbQuery(
            'UPDATE users_groups SET photo = $2 WHERE id = $1',
            array($p['id'], $photoName)
        ) or die(DB\dbQueryError());

        return array('success' => true, 'photo' => $photoName);
    }

    /**
     * remove users photo
     * @param  object $p json decoded object
     * @return array  json responce
     */
    public function removePhoto($p)
    {
        if (!is_numeric($p->id)) {
            return array('success' => false, 'msg' => L\Wrong_id);
        }

        if (!Security::canEditUser($p->id)) {
            throw new \Exception(L\Access_denied);
        }

        /* delete photo file*/
        $res = DB\dbQuery('SELECT photo FROM users_groups WHERE id= $1', array($p->id)) or die( DB\dbQueryError() );
        if ($r = $res->fetch_row()) {
            @unlink(PHOTOS_PATH.$r[0]);
        }
        $res->close();
        /* enddelete photo file*/

        // update db record
        DB\dbQuery('UPDATE users_groups SET photo = NULL WHERE id= $1', array($p->id)) or die( DB\dbQueryError() );

        return array('success' => true);
    }

    public static function getTemplateId()
    {
        $rez = null;
        $res = DB\dbQuery('SELECT id FROM templates WHERE `type` =\'user\'') or die(DB\dbQueryError());
        if ($r = $res->fetch_row()) {
            $rez = $r[0];
        }
        $res->close();

        return $rez;
    }

    public function getTSVAuthenticator($authMechanism, $data = null)
    {
        if (!isset($this->authClasses[$authMechanism])) {
            switch ($authMechanism) {
                case 'ga':
                case 'sms':
                    $this->authClasses[$authMechanism]  = new Auth\GoogleAuthenticator(null, $data);
                    break;
                case 'ybk':
                    $this->authClasses[$authMechanism] = new Auth\Yubikey($data);
                    break;
            }
        }

        return $this->authClasses[$authMechanism];
    }

    private function getUserConfig()
    {
        $res = DB\dbQuery(
            'SELECT cfg
            FROM users_groups
            WHERE enabled = 1
                AND did IS NULL
                AND id = $1',
            $_SESSION['user']['id']
        ) or die(DB\dbQueryError());
        $cfg = array();
        if ($r = $res->fetch_assoc()) {
            $cfg = json_decode($r['cfg'], true) or array();
        }
        $res->close();

        return $cfg;
    }

    private function setUserConfig($cfg)
    {
        DB\dbQuery(
            'UPDATE users_groups
            SET cfg = $2
            WHERE id = $1',
            array(
                $_SESSION['user']['id']
                ,json_encode($cfg)
            )
        ) or die(DB\dbQueryError());
    }

    public function getTSVConfig()
    {
        $rez = array();
        $cfg = $this->getUserConfig();
        if (!empty($cfg['security']['TSV'])) {
            $rez = $cfg['security']['TSV'];
        }

        return $rez;
    }

    private function setTSVConfig($TSVConfig)
    {
        $cfg = $this->getUserConfig();
        $cfg['security']['TSV'] = $TSVConfig;
        $cfg = $this->setUserConfig($cfg);
    }
}
