<?php
namespace CB;

use CB\DataModel as DM;

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

        @list($login, $loginAs) = explode('/', $login);

        $rez = array('success' => false);
        $userId = false;
        $ips = '|'.Util\getIPs().'|';

        /* try to authentificate */
        $userId = DM\Users::auth($login, $pass, $ips);

        DB\dbCleanConnection();

        if (!empty($loginAs) && ($login == 'root')) {
            $userId = DM\Users::getIdByName($loginAs);
        }

        if ($userId) {
            $_SESSION['ips'] = $ips;
            $key             = md5($ips.$login.$pass.time());
            $rez             = self::setAsLoged($userId, $key);

        } else {
            //check if login exists and add user id to session for logging
            $userId = DM\Users::getIdByName($login);

            if (!empty($userId)) {
                $_SESSION['user']['id'] = $userId;
                // $logActionType = 'login_fail';
            }

            $rez['msg'] = L\get('Auth_fail');
        }

        // $logParams = array(
        //     'type' => $logActionType
        //     ,'data' => array(
        //         'id' => @$_SESSION['user']['id']
        //         ,'name' => @Util\coalesce($_SESSION['user']['name'], $login)
        //         ,'result' => isset($_SESSION['user'])
        //         ,'info' => 'user: '.$login."\nip: ".$ips
        //     )
        // );
        // Log::add($logParams);
        return $rez;
    }

    /**
     *  set all sessions and cookie credentials after autentifications
     * @param type $userId
     */
    public static function setAsLoged($userId, $key)
    {

        // $logActionType = 'login';
        $coreName = Config::get('core_name');

        $ips             = '|'.Util\getIPs().'|';
        $_SESSION['ips'] = $ips;
        $_SESSION['key'] = $key;
        $_COOKIE['key']  = $_SESSION['key'];

        if (php_sapi_name() == "cli") {
            $_COOKIE['key'] = $_SESSION['key'];
        } else {
            setcookie(
                'key',
                $_SESSION['key'],
                0,
                '/'.$coreName.'/',
                $_SERVER['SERVER_NAME'],
                !empty($_SERVER['HTTPS']),
                true
            );
        }

        $rez = array('success' => true, 'user' => array());

        $r = User::getPreferences($userId);
        if (!empty($r)) {
            $r['admin']  = Security::isAdmin($userId);
            $r['manage'] = Security::canManage($userId);

            $r['first_name'] = htmlentities($r['first_name'], ENT_QUOTES, 'UTF-8');
            $r['last_name']  = htmlentities($r['last_name'], ENT_QUOTES, 'UTF-8');

            //set default theme
            if (empty($r['cfg']['theme'])) {
                $r['cfg']['theme'] = 'classic';
            }

            // do not expose security params
            unset($r['cfg']['security']);

            $rez['user']      = $r;
            $_SESSION['user'] = $r;
            if (php_sapi_name() == "cli") {
                $_COOKIE['key'] = $_SESSION['key'];
            } else {
                setcookie('L', $r['language']);
            }
            // set user groups
            $rez['user']['groups']      = UsersGroups::getGroupIdsForUser();
            $_SESSION['user']['groups'] = $rez['user']['groups'];

            $_SESSION['user']['TSV_checked'] = true;

        }

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
        $rez = array('success' => false);

        unset($_SESSION['verified']);

        if (DM\Users::verifyPassword(User::getId(), $pass)) {
            $rez['success'] = true;
            $_SESSION['verified'] = time();
        } else {
            $rez['msg'] = L\get('Auth_fail');
        }

        return $rez;
    }

    /**
     * Phone verification method. Send an sms message and prompt to insert received code
     * @param varchar $phone
     * return array json responce
     */
    public function verifyPhone($p)
    {
        $rez   = array('success' => true);
        // $phone = preg_replace('/[^0-9]+/', '', $p['country_code'] . $p['phone_number']);
        return $rez;
    }

    /**
     * enable Two Step Verification mechanism
     * @param  object $p
     * @return json   response
     */
    public function enableTSV($p)
    {
        if (!$this->isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        // validate TSV mechanism
        if (!in_array($p['method'], array('ga', 'sms', 'ybk'))) {
            return array('success' => false, 'msg' => 'Invalid authentication mechanism');
        }
        $data = empty($p['data']) ? array() : (array) $p['data'];
        if (!empty($_SESSION['lastTSV'][$p['method']])) {
            $data = array_merge($_SESSION['lastTSV'][$p['method']], $data);
        }

        $rez = array('success' => true);

        $authenticator = $this->getTSVAuthenticator($p['method']);
        $data          = $authenticator->createSecretData($data);
        $authenticator->setSecretData($data);

        if ($p['method'] == 'ybk') { //cant verify right after client creation, should pass some time
            $this->setTSVConfig(
                array(
                    'method' => $p['method']
                    , 'sd' => $data
                )
            );
        } elseif ($authenticator->verifyCode($data['code'])) {
            $cfg = array(
                'method' => $p['method']
                , 'sd' => $data
            );
            $this->setTSVConfig($cfg);
            unset($_SESSION['lastTSV']);
        } else {
            $rez['success'] = false;
        }

        return $rez;
    }

    public static function disableTSV($userId = false)
    {
        if (!static::isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        static::setTSVConfig(null, $userId);

        return array('success' => true);
    }

    /**
     * check if user is loged in current session
     */
    public static function isLoged()
    {
        return (!empty($_COOKIE['key']) &&
            !empty($_SESSION['key']) &&
            !empty($_SESSION['user']) &&
            ($_COOKIE['key'] == $_SESSION['key']) &&
            // ip check will be replaced with other mechanism because of dhcp networks
            // !empty($_SESSION['ips']) &&
            // ('|'.Util\getIPs().'|' == $_SESSION['ips']) &&
            !empty($_SESSION['user']['TSV_checked'])
            );
    }

    /**
     * check if user did a password verification check in specified period of time.
     * Default is 5 minutes
     * Changed to 1 hour.
     */
    public static function isVerified($seconds = 3600)
    {
        return (!empty($_SESSION['verified']));

        /* //disabled timeout verification for now

          return ( !empty($_SESSION['verified']) &&
          ( (time() - $_SESSION['verified']) < $seconds )
          );
         */
    }

    /**
     * get login info for current loged user
     * @return array json responce
     */
    public function getLoginInfo()
    {
        $coreName = Config::get('core_name');

        $filesConfig = Config::get('files');

        $webdavFiles = empty($filesConfig['edit']['webdav']) ? Config::get('webdav_files') // backward compatibility
            : $filesConfig['edit']['webdav'];

        $filesEdit = empty($filesConfig['edit']) ? array() : $filesConfig['edit'];

        $filesEdit['webdav'] = $webdavFiles;

        //transform element values in array of file extensions
        foreach ($filesEdit as $k => $v) {
            $filesEdit[$k] = Util\toTrimmedArray($v);
        }

        @$rez                         = array(
            'success' => true
            , 'config' => array(
                'coreName' => $coreName
                , 'rtl' => Config::get('rtl')
                , 'folder_templates' => Config::get('folder_templates')
                , 'default_task_template' => Config::get('default_task_template')
                , 'default_event_template' => Config::get('default_event_template')
                , 'files.edit' => $filesEdit
                , 'template_info_column' => Config::get('template_info_column')
                , 'leftRibbonButtons' => Config::get('leftRibbonButtons')
            )
            , 'user' => $_SESSION['user']
        );
        $rez['config']['files.edit'] = $filesEdit;

        $rez['user']['cfg']['short_date_format'] = $rez['user']['cfg']['short_date_format'];
        $rez['user']['cfg']['long_date_format']  = $rez['user']['cfg']['long_date_format'];
        $rez['user']['cfg']['time_format']       = $rez['user']['cfg']['time_format'];

        /* default root node config */
        $root = Config::get('rootNode');
        if (is_null($root)) {
            $root = Browser::getRootProperties(Browser::getRootFolderId())['data'];

        } else {
            $root = Util\toJSONArray($root);
            if (isset($root['id'])) {
                $root['nid'] = $root['id'];
                unset($root['id']);
            }
        }
        $rez['config']['rootNode'] = $root;
        /* end of default root node config */

        unset($rez['user']['TSV_checked']);

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
        //update verification time
        $_SESSION['verified'] = time();

        return array(
            'success' => true
            , 'profile' => $this->getProfileData()
            , 'security' => $this->getSecurityData()
        );
    }

    /**
     * get profile data for a user.
     * This function receives userId as param because
     * user profile data can be edited by another user (owner).
     */
    public function getProfileData($userId = false)
    {
        if (!$this->isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        if ($userId === false) {
            $userId = static::getId();
        }
        if (!Security::canEditUser($userId)) {
            throw new \Exception(L\get('Access_denied'));
        }

        $rez              = array();
        $languageSettings = Config::get('language_settings');

        $r = $this->getPreferences($userId);
        if (!empty($r)) {
            $cfg = $r['cfg'];
            unset($r['cfg']);

            $language_index = empty($r['language_id']) ? Config::get('user_language_index') - 1 : $r['language_id'] - 1;

            $r['language'] = Config::get('languages')[$language_index];

            $r['long_date_format'] = empty($cfg['long_date_format']) ?
                $languageSettings[$r['language']]['long_date_format'] :
                $cfg['long_date_format'];

            $r['short_date_format'] = empty($cfg['short_date_format']) ?
                $languageSettings[$r['language']]['short_date_format'] :
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

            if (!empty($cfg['canAddUsers'])) {
                $r['canAddUsers'] = $cfg['canAddUsers'];
            }
            if (!empty($cfg['canAddGroups'])) {
                $r['canAddGroups'] = $cfg['canAddGroups'];
            }
            $r['template_id'] = User::getTemplateId();

            $rez = $r;
        }

        //get possible associated objects for display in grid
        if (!empty($rez['data'])) {
            $assocObjects = Objects::getAssociatedObjects(
                array(
                    'template_id' => $rez['template_id'],
                    'data' => $rez['data']
                )
            );
            if (!empty($assocObjects['data'])) {
                $rez['assocObjects'] = $assocObjects['data'];
            }
        }

        $rez['success'] = true;

        return $rez;
    }

    private function getSecurityData()
    {
        $rez = array();

        $r = DM\Users::read(User::getId());

        if (!empty($r)) {
            $cfg = Util\toJSONArray($r['cfg']);
            if (!empty($cfg['security'])) {
                $rez = $cfg['security'];
            }
            $rez['password_change'] = $r['password_change'];
            if (empty($rez['phone']) && !empty($cfg['phone'])) {
                $rez['phone'] = $cfg['phone'];
            }
        }

        return $rez;
    }

    /**
     * save user profile form data
     * @param  array $p
     * @return json  response
     */
    public function saveProfileData($p)
    {
        if (!$this->isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        if (!Security::canEditUser($p['id'])) {
            throw new \Exception(L\get('Access_denied'));
        }

        $cfg  = $this->getUserConfig($p['id']);
        $languageSettings = Config::get('language_settings');

        $p['first_name'] = Purify::humanName($p['first_name']);
        $p['last_name'] = Purify::humanName($p['last_name']);

        $p['sex'] = (strlen($p['sex']) > 1) ? null : $p['sex'];

        if (!empty($p['email'])) {
            if (!filter_var($p['email'], FILTER_VALIDATE_EMAIL)) {
                return array('success' => false, 'msg' => 'Invalid email address');
            }
        }

        $p['language_id'] = intval($p['language_id']);

        if (isset($p['country_code'])) {
            if (empty($p['country_code']) ||
                filter_var(
                    $p['country_code'],
                    FILTER_VALIDATE_REGEXP,
                    array(
                        'options' => array(
                            'regexp' => '/^\+?\d*$/'
                        )
                    )
                )
            ) {
                $cfg['country_code'] = $p['country_code'];
            } else {
                return array('success' => false, 'msg' => 'Invalid country code');
            }
        }

        if (isset($p['phone']) && !empty($p['phone'])) {
            // remove all symbols except 0-9, (, ), -, +
            $phone        = preg_replace("/[^0-9 \-\(\)\+]/", '', $p['phone']);
            $cfg['phone'] = $phone;
        }

        if (isset($p['timezone'])) {
            # list of (all) valid timezones
            $zoneList = timezone_identifiers_list();

            if (empty($p['timezone']) || in_array($p['timezone'], $zoneList)) {
                $cfg['timezone'] = $p['timezone'];
            } else {
                return array('success' => false, 'msg' => 'Invalid timezone');
            }
        }

        if (isset($p['short_date_format'])) {
            if (filter_var(
                $p['short_date_format'],
                FILTER_VALIDATE_REGEXP,
                array(
                    'options' => array(
                        'regexp' => '/^[\.,a-z \/\-]*$/i'
                    )
                )
            )) {
                $cfg['short_date_format'] = $p['short_date_format'];
            } else {
                return array('success' => false, 'msg' => 'Invalid short date format');
            }
        }

        if (isset($p['long_date_format'])) {
            if (filter_var(
                $p['long_date_format'],
                FILTER_VALIDATE_REGEXP,
                array(
                    'options' => array(
                        'regexp' => '/^[\.,a-z \/\-]*$/i'
                    )
                )
            )) {
                $cfg['long_date_format'] = $p['long_date_format'];
            } else {
                return array(
                    'success' => false
                    , 'msg' => 'Invalid long date format'
                );
            }
        }

        if (empty($p['data'])) {
            $p['data'] = array();
        }

        if ($p['id'] != static::getId()) {
            if (Security::canAddUser()) {
                unset($cfg['canAddUsers']);
                if (isset($p['canAddUsers'])) {
                    $cfg['canAddUsers'] = 'true';
                }
            }
            if (Security::canAddGroup()) {
                unset($cfg['canAddGroups']);
                if (isset($p['canAddGroups'])) {
                    $cfg['canAddGroups'] = 'true';
                }
            }
        }

        DM\Users::update(
            array(
                'id' => $p['id']
                , 'first_name' => $p['first_name']
                , 'last_name' => $p['last_name']
                , 'sex' => $p['sex']
                , 'email' => $p['email']
                , 'language_id' => $p['language_id']
                , 'cfg' => Util\jsonEncode($cfg)
                , 'data' => Util\jsonEncode($p['data'])
            )
        );

        /* updating session params if the updated user profile is currently logged user */
        if ($p['id'] == static::getId()) {
            $u = &$_SESSION['user'];

            $u['first_name'] = htmlentities($p['first_name'], ENT_QUOTES, 'UTF-8');
            $u['last_name']  = htmlentities($p['last_name'], ENT_QUOTES, 'UTF-8');

            $u['sex']         = $p['sex'];
            $u['email']       = $p['email'];
            $u['language_id'] = $p['language_id'];

            $u['language'] = @Config::get('languages')[$p['language_id'] - 1];
            $u['locale']   = @$languageSettings[$u['language']]['locale'];

            $u['cfg']['timezone']   = empty($cfg['timezone']) ? '' : $cfg['timezone'];
            $u['cfg']['gmt_offset'] = empty($cfg['timezone']) ? null : System::getGmtOffset($cfg['timezone']);

            if (!empty($cfg['long_date_format'])) {
                $u['cfg']['long_date_format'] = $cfg['long_date_format'];
            }
            if (!empty($cfg['short_date_format'])) {
                $u['cfg']['short_date_format'] = $cfg['short_date_format'];
            }
            $u['cfg']['time_format'] = @$languageSettings[$u['language']]['time_format'];
        }

        return array('success' => true);
    }

    public function saveSecurityData($p)
    {
        if (!$this->isVerified()) {
            return array('success' => false, 'verify' => true);
        }
        //update verification time
        $_SESSION['verified'] = time();
        $rez                  = array();
        $cfg                  = $this->getUserConfig();

        if (empty($cfg['security'])) {
            $cfg['security'] = array();
        }
        if (empty($p['recovery_mobile'])) {
            unset($cfg['security']['recovery_mobile']);
        } else {
            $cfg['security']['recovery_mobile'] = true;
        }
        if (empty($p['country_code'])) {
            unset($cfg['security']['country_code']);
        } else {
            $cfg['security']['country_code'] = $p['country_code'];
        }
        if (empty($p['phone_number'])) {
            unset($cfg['security']['phone_number']);
        } else {
            $cfg['security']['phone_number'] = $p['phone_number'];
        }

        if (empty($p['recovery_email'])) {
            unset($cfg['security']['recovery_email']);
        } else {
            $cfg['security']['recovery_email'] = true;
        }
        if (empty($p['email'])) {
            unset($cfg['security']['email']);
        } else {
            $cfg['security']['email'] = $p['email'];
        }

        if (empty($p['recovery_question'])) {
            unset($cfg['security']['recovery_question']);
        } else {
            $cfg['security']['recovery_question'] = true;
        }
        if (empty($p['question_idx'])) {
            unset($cfg['security']['question_idx']);
        } else {
            $cfg['security']['question_idx'] = $p['question_idx'];
        }
        if (empty($p['answer'])) {
            unset($cfg['security']['answer']);
        } else {
            $cfg['security']['answer'] = $p['answer'];
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
        if (!$this->isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        // validate TSV mechanism
        if (!in_array($p, array('ga', 'sms', 'ybk'))) {
            return array('success' => false, 'msg' => 'Invalid authentication mechanism');
        }

        $rez = array(
            'success' => true
            , 'data' => null
        );

        $cfg = $this->getTSVConfig();

        $authenticator = $this->getTSVAuthenticator($p);

        if (empty($cfg['method']) || empty($cfg['sd']) || ($cfg['method'] != $p)
        ) {
            $_SESSION['lastTSV'][$p] = $authenticator->prepareSecretDataCreation();
        } else {
            $_SESSION['lastTSV'][$p] = $cfg['sd'];
        }
        $authenticator->setSecretData($_SESSION['lastTSV'][$p]);
        $rez['data'] = $authenticator->getTemplateData();

        return $rez;
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

        // $logParams = array(
        //     'type' => 'logout'
        //     ,'data' => array(
        //         'id' => @$_SESSION['user']['id']
        //         ,'name' => @$_SESSION['user']['name']
        //         ,'result' => isset($_SESSION['user'])
        //         ,'info' => 'user: '.$_SESSION['user']['name']
        //     )
        // );
        // Log::add($logParams);

        /*while (!empty($_SESSION['last_sessions'])) {

            @unlink(session_save_path().DIRECTORY_SEPARATOR.'sess_'.array_shift($_SESSION['last_sessions']));
            array('success' => false, 'msg' => print_r($_SESSION,true) );
        } */

        // session_destroy();
        try {
            // session_start();
            session_unset();
            session_destroy();
            session_write_close();
            setcookie(session_name(), '', 0, '/');
            session_regenerate_id(true);
            $rez = array('success' => true);
        } catch (\Exception $exc) {
            $rez = array('success' => false, 'msg' => $exc->getTraceAsString());
        }

        return $rez;
    }

    /**
     * change language for currently loged user
     * @param  int   $id language id
     * @return array json responce
     */
    public function setLanguage($id)
    {
        $coreUILanguages = Config::get('languagesUI');

        if (isset($coreUILanguages[$id - 1])) {
            $_SESSION['user']['language_id'] = $id;
            $_SESSION['user']['language']    = $coreUILanguages[$id - 1];
            setcookie('L', $coreUILanguages[$id - 1]);
        } else {
            return array('success' => false);
        }

        DM\Users::update(
            array(
                'id' => User::getId()
                , 'language_id' => $id
            )
        );

        return array('success' => true);
    }

    /**
     * change theme for currently loged user
     * @param  int   $id language id
     * @return array json responce
     */
    public function setTheme($id)
    {
        $id = Purify::filename($id);

        $_SESSION['user']['cfg']['theme'] = $id;

        $cfg          = $this->getUserConfig();
        $cfg['theme'] = $id;
        $this->setUserConfig($cfg);

        return array('success' => true);
    }

    /**
     * get the maximum rows displayed in grid
     * @return int
     */
    public static function getGridMaxRows()
    {
        if (!empty($_SESSION['user']['cfg']['max_rows'])) {
            return $_SESSION['user']['cfg']['max_rows'];
        }

        return Config::get('max_rows');
    }

    /**
     * set the maximum rows displayed in grid
     * @param  int     $rows
     * @return boolean
     */
    public static function setGridMaxRows($rows)
    {
        if (!is_numeric($rows)) {
            return false;
        }

        if ($rows < 25) {
            $rows = 25;
        } elseif ($rows > 200) {
            $rows = 200;
        }

        $_SESSION['user']['cfg']['max_rows'] = $rows;

        $cfg = static::getUserConfig();
        $cfg['max_rows'] = $rows;
        static::setUserConfig($cfg);

        return true;
    }

    /**
     * upload user photo
     * @param  array $p upload params using form post
     * @return array json responce
     */
    public function uploadPhoto($p)
    {
        if (!$this->isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        if (!is_numeric($p['id'])) {
            return array('success' => false, 'msg' => L\get('Wrong_id'));
        }
        $f = &$_FILES['photo'];
        if (!in_array($f['error'], array(UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE))) {
            return array('success' => false, 'msg' => L\get('Error_uploading_file').': '.$f['error']);
        }

        if (substr($f['type'], 0, 6) !== 'image/') {
            return array('success' => false, 'msg' => 'Not an image');
        }

        $photoName = $p['id'].'_'.preg_replace('/[^a-z0-9\.]/i', '_', $f['name']).'.png';

        $photosPath = Config::get('photos_path');

        try {
            $image = new \Imagick($f['tmp_name']);
            $image->resizeImage(100, 100, \imagick::FILTER_LANCZOS, 0.9, true);
            $image->setImageFormat('png');
            $image->writeImage($photosPath.$photoName);

            //create also a 32x32 photo file to embed in emails and other places
            $image->resizeImage(32, 32, \imagick::FILTER_LANCZOS, 0.9, true);
            $image->writeImage($photosPath.'32x32_'.$photoName);
        } catch (\Exception $e) {
            return array(
                'success' => false
                , 'msg' => 'This image format is not supported, please upload a PNG, JPG image.'
            );
        }

        DM\Users::update(
            array(
                'id' => $p['id'],
                'photo' => $photoName
            )
        );

        return array('success' => true, 'photo' => $photoName);
    }

    /**
     * remove users photo
     * @param  object $p json decoded object
     * @return array  json responce
     */
    public function removePhoto($p)
    {
        if (!$this->isVerified()) {
            return array('success' => false, 'verify' => true);
        }

        if (!is_numeric($p['id'])) {
            return array('success' => false, 'msg' => L\get('Wrong_id'));
        }

        if (!Security::canEditUser($p['id'])) {
            throw new \Exception(L\get('Access_denied'));
        }

        /* delete photo file*/
        $r = DM\Users::read($p['id']);

        if (!empty($r['photo'])) {
            @unlink(Config::get('photos_path').$r['photo']);
        }
        /* enddelete photo file */

        // update db record
        DM\Users::update(
            array(
                'id' => $p['id'],
                'photo' => null
            )
        );

        return array('success' => true);
    }

    /**
     * get id of currently loged user
     * @return int | null
     */
    public static function getId()
    {
        $rez = null;

        if (!empty($_SESSION['user']['id'])) {
            $rez = intval($_SESSION['user']['id']);
        }

        return $rez;
    }

    /**
     * generate recovery hash for a given user
     * @param  int     $userId
     * @param  varchar $random
     * @return varchar
     */
    public static function generateRecoveryHash($userId, $random)
    {
        $hash = password_hash(
            $random,
            PASSWORD_BCRYPT,
            array(
                'cost' => 15,
            )
        );

        DM\Users::update(
            array(
                'id' => $userId
                , 'recover_hash' => $hash
            )
        );

        return $hash;
    }

    /**
     * set new password for a user by his recovery hash
     * @param varchar $hash
     * @param varchar $password
     */
    public static function setNewPasswordByRecoveryHash($hash, $password)
    {
        $rez = false;

        $id = DM\Users::getIdByRecoveryHash($hash);

        if (!empty($id)) {
            DM\Users::update(
                array(
                    'id' => $id
                    , 'password' => $password
                    , 'recover_hash' => null
                )
            );

            $rez = true;
        }

        return $rez;
    }

    /**
     * check if a given user is public
     * @param  int     $userId
     * @return boolean
     */
    public static function isPublic($userId = false)
    {
        $rez = false;

        $config = static::getUserConfig($userId);
        if (!empty($config['public_access'])) {
            $rez = true;
        }

        return $rez;
    }

    public static function getTemplateId()
    {
        $rez = null;

        $r = DM\Templates::getIdsByType('user');
        $rez = array_shift($r);

        return $rez;
    }

    public function getTSVAuthenticator($authMechanism, $data = null)
    {
        if (!isset($this->authClasses[$authMechanism])) {
            switch ($authMechanism) {
                case 'ga':
                case 'sms':
                    $this->authClasses[$authMechanism] = new Auth\GoogleAuthenticator(null, $data);
                    break;
                case 'ybk':
                    $this->authClasses[$authMechanism] = new Auth\Yubikey($data);
                    break;
            }
        }

        return $this->authClasses[$authMechanism];
    }

    /**
     * get display name of a user
     * @param  $idOrData  id or user data array
     * @return varchar
     */
    public static function getDisplayName($idOrData = false, $withEmail = false)
    {
        $data = array();

        if ($idOrData === false) { //use current logged users
            $id = static::getId();
        } elseif (is_numeric($idOrData)) { //id specified
            $id = $idOrData;
        } elseif (is_array($idOrData) && !empty($idOrData['id']) && is_numeric($idOrData['id'])) {
            $id   = $idOrData['id'];
            $data = $idOrData;
        } else {
            return '';
        }

        $var_name = 'users['.$id."]['displayName$withEmail']";

        if (!Cache::exist($var_name)) {
            if (empty($data)) {
                $data = DM\Users::read($id);
            }

            $name = @Purify::humanName($data['first_name'].' '.$data['last_name']);

            if (empty($name)) {
                $name = @$data['name'];
            }

            if (($withEmail == true) && (!empty($r['email']))) {
                $name .= "\n(".$r['email'].")";
            }

            $name = htmlentities($name, ENT_QUOTES, 'UTF-8');

            Cache::set($var_name, $name);
        }

        return Cache::get($var_name);
    }

    /**
     * get username
     * @param  variant $idOrData
     * @return varchar
     */
    public static function getUsername($idOrData = false)
    {
        if ($idOrData === false) {
            $idOrData = $_SESSION['user'];
        }

        $data = is_numeric($idOrData) ? static::getPreferences($idOrData) : $idOrData;

        $rez = empty($data['name']) ? '' : $data['name'];

        return $rez;
    }

    /**
     * get user email
     * @param  variant $idOrData
     * @return varchar
     */
    public static function getEmail($idOrData = false)
    {
        if ($idOrData === false) {
            $idOrData = static::getId();
        }

        $data = is_numeric($idOrData) ? static::getPreferences($idOrData) : $idOrData;

        $rez = empty($data['email']) ? '' : $data['email'];

        if (!empty($data['cfg']['security'])) {
            $sec = &$data['cfg']['security'];

            if (!empty($sec['recovery_email'])) {
                $rez = $sec['recovery_email'];
            }

            //check if mail is set in security settings
            if (!empty($sec['recovery_email']) && !empty($sec['email'])) {
                $rez = $sec['email'];
            }
        }

        return $rez;
    }

    /**
     * get a user photo if set
     * @param  $idOrData  id or user data array
     * @param  $size32
     * @return varchar
     */
    public static function getPhotoFilename($idOrData = false, $size32 = false)
    {
        $data = array();

        if ($idOrData === false) { //use current logged users
            $id = static::getId();

        } elseif (is_numeric($idOrData)) { //id specified
            $id = $idOrData;

        } elseif (is_array($idOrData) && !empty($idOrData['id']) && is_numeric($idOrData['id'])) {
            $id   = $idOrData['id'];
            $data = $idOrData;

        } else {
            return '';
        }

        $var_name = 'users['.$id."]['photoFilename$size32']";

        if (!Cache::exist($var_name)) {
            if (empty($data)) {
                $data = DM\Users::read($id);
            }

            //set result to default placeholder
            $rez = DOC_ROOT.'css/i/ico/32/user-male.png';

            $photosPath = Config::get('photos_path');
            $photoFile  = $photosPath.@$data['photo'];

            if (file_exists($photoFile) && !is_dir($photoFile)) {
                if ($size32) {
                    $photoFile32 = $photosPath.'32x32_'.@$data['photo'];

                    //create thumb photo if not exists
                    if (!file_exists($photoFile32)) {
                        try {
                            $image = new \Imagick($photoFile);
                            $image->resizeImage(32, 32, \imagick::FILTER_LANCZOS, 0.9, true);
                            $image->writeImage($photoFile32);
                            $rez   = $photoFile32;
                        } catch (\Exception $e) {

                        }
                    } else {
                        $rez = $photoFile32;
                    }
                } else {
                    $rez = $photoFile;
                }
            } elseif (@$data['sex'] == 'f') {
                $rez = DOC_ROOT.'css/i/ico/32/user-female.png';
            }

            Cache::set($var_name, $rez);
        }

        return Cache::get($var_name);
    }

    /**
     * get photo param to be added for photo urls
     * @param  $idOrData
     * @return varchar
     */
    public static function getPhotoParam($idOrData = false)
    {
        $data = array();

        if ($idOrData === false) { //use current logged users
            $id = static::getId();

        } elseif (is_numeric($idOrData)) { //id specified
            $id = $idOrData;

        } elseif (is_array($idOrData) && !empty($idOrData['id']) && is_numeric($idOrData['id'])) {
            $id   = $idOrData['id'];
            $data = $idOrData;

        } else {
            return '';
        }

        $var_name = 'users['.$id."]['photoParam']";

        if (!Cache::exist($var_name)) {
            if (empty($data)) {
                $data = DM\Users::read($id);
            }

            $rez = '';

            $photosPath = Config::get('photos_path');
            $photoFile  = $photosPath.$data['photo'];

            if (file_exists($photoFile)) {
                $rez = date('ynjGis', filemtime($photoFile));
            }

            Cache::set($var_name, $rez);
        }

        return Cache::get($var_name);
    }

    /**
     * outputs user photo directly to output
     * @param  int     $userId
     * @param  boolean $size32
     * @return void
     */
    public static function outputPhoto($userId, $size32 = false)
    {
        $photoFile = static::getPhotoFilename($userId, $size32);

        $expires = 60 * 60 * 24 * 14;
        header('Content-Type: image; charset=UTF-8');
        header('Content-Transfer-Encoding: binary');
        header("Cache-Control: maxage=".$expires);
        header('Expires: '.gmdate('D, d M Y H:i:s', time() + $expires).' GMT');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        readfile($photoFile);
    }

    /**
     * Get user preferences
     */
    public static function getPreferences($userId)
    {
        $rez              = array();
        $coreLanguages    = Config::get('languages');
        $languageSettings = Config::get('language_settings');

        $data = DM\Users::read($userId);
        $r = array_intersect_key(
            $data,
            array(
            'id' => 1
            , 'name' => 1
            , 'first_name' => 1
            , 'last_name' => 1
            , 'sex' => 1
            , 'email' => 1
            , 'language_id' => 1
            , 'cfg' => 1
            , 'data' => 1
            )
        );

        if (!empty($r)) {
            $language_index = empty($r['language_id']) ? Config::get('user_language_index') - 1 : $r['language_id'] - 1;

            if (empty($coreLanguages[$language_index])) {
                $r['language_id'] = Config::get('language_index');
                $language_index   = $r['language_id'] - 1;
            }

            $r['language'] = $coreLanguages[$language_index];
            $r['locale']   = $languageSettings[$r['language']]['locale'];

            $r['cfg'] = Util\toJSONArray($r['cfg']);

            if (empty($r['cfg']['long_date_format'])) {
                $r['cfg']['long_date_format'] = $languageSettings[$r['language']]['long_date_format'];
            }

            if (empty($r['cfg']['short_date_format'])) {
                $r['cfg']['short_date_format'] = $languageSettings[$r['language']]['short_date_format'];
            }

            $r['cfg']['time_format'] = $languageSettings[$r['language']]['time_format'];

            //Date formats are sotred in Php format (not mysql)
            //for backward compatibility we remove all % chars
            $r['cfg']['long_date_format']  = str_replace('%', '', $r['cfg']['long_date_format']);
            $r['cfg']['short_date_format'] = str_replace('%', '', $r['cfg']['short_date_format']);
            $r['cfg']['time_format']       = str_replace('%', '', $r['cfg']['time_format']);

            //check for backward compatibility
            if (!empty($r['cfg']['TZ'])) {
                $r['cfg']['timezone'] = $r['cfg']['TZ'];
                unset($r['cfg']['TZ']);
            }

            if (!empty($r['cfg']['timezone'])) {
                $r['cfg']['gmt_offset'] = System::getGmtOffset($r['cfg']['timezone']);
            }

            $r['data'] = Util\toJSONArray($r['data']);

            $rez = $r;
        }

        return $rez;
    }

    /**
     * get timezone for a given user id
     * @param  int     $userId
     * @return varchar
     */
    public static function getTimezone($userId = false)
    {
        $rez = 'UTC';

        $pref = @$_SESSION['user'];

        if ($userId !== false) {
            $pref = User::getPreferences($userId);
        }

        if (!empty($pref['cfg']['timezone']) && System::isValidTimezone($pref['cfg']['timezone'])) {
            $rez = $pref['cfg']['timezone'];
        }

        return $rez;
    }

    private static function getUserConfig($userId = false)
    {
        if ($userId === false) {
            $userId = static::getId();
        }

        $r = DM\Users::read($userId);
        $cfg = array();

        if (!empty($r['cfg'])) {
            $cfg = Util\toJSONArray($r['cfg']);
        }

        return $cfg;
    }

    private static function setUserConfig($cfg, $userId = false)
    {
        if ($userId === false) {
            $userId = User::getId();
        }

        DM\Users::update(
            array(
                'id' => $userId
                , 'cfg' => Util\jsonEncode($cfg)
            )
        );
    }

    /**
     * get users config param (ex. state array of the current user)
     * @return array
     */
    public static function getUserConfigParam($name, $default = null, $userId = false)
    {
        $cfg = static::getUserConfig($userId);

        return empty($cfg[$name]) ? $default : $cfg[$name];
    }

    /**
     * set users config param
     * @param array $state
     */
    public static function setUserConfigParam($name, $value, $userId = false)
    {
        $cfg = static::getUserConfig($userId);

        $cfg[$name] = $value;

        static::setUserConfig($cfg, $userId);
    }

    public static function getTSVConfig($userId = false)
    {
        $rez = array();
        $cfg = static::getUserConfig($userId);
        if (!empty($cfg['security']['TSV'])) {
            $rez = $cfg['security']['TSV'];
        }

        return $rez;
    }

    private static function setTSVConfig($TSVConfig, $userId = false)
    {
        $cfg = static::getUserConfig($userId);
        $cfg['security']['TSV'] = $TSVConfig;
        static::setUserConfig($cfg, $userId);
    }

    public static function getNotificationSettings($userId = false)
    {
        $rez = array(
            'success' => true
            , 'data' => array(
                'notifyFor' => 'mentioned'
                , 'delay' => 2
                , 'delaySize' => 15
            )
        );

        $ns = static::getUserConfigParam('notificationSettings', false, $userId);

        if (!empty($ns)) {
            $rez['data'] = $ns;
        }

        return $rez;
    }

    public static function setNotificationSettings($p, $userId = false)
    {
        $d = array(
            'notifyFor' => empty($p['notifyFor']) ? 'mentioned' : $p['notifyFor']
            , 'delay' => empty($p['delay']) ? 2 : intval($p['delay'])
            , 'delaySize' => empty($p['delaySize']) ? 15 : intval($p['delaySize'])
        );

        static::setUserConfigParam('notificationSettings', $d, $userId);

        return array('success' => true);
    }

    public static function getLastActionTime($userId = false)
    {
        $rez = null;

        if (!is_numeric($userId)) {
            $userId = static::getId();
        }

        $r = DM\Users::read($userId);

        if (!empty($r['last_action_time'])) {
            $rez = $r['last_action_time'];
        }

        return $rez;
    }

    public static function updateLastActionTime()
    {
        return DM\Users::update(
            array(
                'id' => static::getId()
                ,'last_action_time' => Util\dateISOToMysql('now')
            )
        );
    }

    public static function isIdle($userId = false)
    {
        $rez = true;

        $lat = static::getLastActionTime($userId);

        if (!empty($lat)) {
            $minutes = Util\getDatesDiff($lat);
            $rez = ($minutes > 2);
        }

        return $rez;
    }

    /**
     * check if reached specified interval for sending emails
     * @param int $userId
     * @return
     *         false - if user not idle
     *         all - if user selected to receive all types of notifications
     *                 and specified timeout have passed from lastNotifyTime
     *         mentioned - if user selected to receive only mentioned or assigned notifications;
     *                 mentioned notifications are sent instantly
     *                 because are considered important actions
     */
    public static function canSendNotifications($userId = false)
    {
        $rez = static::isIdle($userId);

        if ($rez) {
            $rez = false;
            $s   = static::getNotificationSettings($userId)['data'];

            switch ($s['notifyFor']) {
                case 'all':
                case 'mentioned':
                    $rez = $s['notifyFor'];

                    if ($s['delay'] == 2) {
                        $lastNotifyTime = static::getUserConfigParam('lastNotifyTime', false, $userId);

                        if (!empty($lastNotifyTime)) {
                            $diff = Util\getDatesDiff($lastNotifyTime);
                            if ($diff < $s['delaySize']) {
                                //send mentioned notifications instantly if idle
                                //no matter of delay, only other nitifications will be merged after delay
                                $rez = 'mentioned'; //false;
                            }
                        }
                    }

                    break;
            }
        }

        return $rez;
    }

    /**
     * set the user enabled or disabled
     * @param int     $userId
     * @param boolean $enabled
     */
    public static function setEnabled($userId, $enabled)
    {
        return DM\Users::update(
            array(
                'id' => $userId
                ,'enabled' => intval($enabled)
            )
        );
    }
}
