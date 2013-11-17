<?php
namespace CB;

class Files
{
    public function getProperties($id)
    {
        $rez = array('success' => true, 'data' => array());

        $file = new Objects\File($id);
        $rez['data'] = $file->load();

        $d = &$rez['data'];
        $d['path'] = str_replace(',', '/', $d['path']);
        $a = explode('.', $d['name']);
        $d['ago_date'] = date(
            str_replace(
                '%',
                '',
                $_SESSION['user']['cfg']['long_date_format']
            ),
            strtotime($d['cdate'])
        ).
        ' '.L\at.' '.
        date(
            str_replace(
                '%',
                '',
                $_SESSION['user']['cfg']['time_format']
            ),
            strtotime($d['cdate'])
        );

        $d['ago_date'] = Util\translateMonths($d['ago_date']);
        $d['ago_text'] = Util\formatAgoTime($d['cdate']);

        if (!empty($d['versions'])) {
            foreach ($d['versions'] as &$r) {
                $r['ago_date'] = date(
                    str_replace(
                        '%',
                        '',
                        $_SESSION['user']['cfg']['long_date_format']
                    ),
                    strtotime($r['cdate'])
                ).' '.L\at.' '.
                date(
                    str_replace(
                        '%',
                        '',
                        $_SESSION['user']['cfg']['time_format']
                    ),
                    strtotime($r['cdate'])
                );

                $r['ago_date'] = Util\translateMonths($r['ago_date']);
                $r['ago_text'] = Util\formatAgoTime($r['cdate']);
            }
        }

        return $rez;
    }

    public function saveProperties($p)
    {

        // SECURITY: check if current user has write access
        if (!Security::canWrite($p['id'])) {
            throw new \Exception(L\Access_denied);
        }
        $file = new Objects\File($d['id']);
        $file->setData($p);
        $file->save();

        return array('success' => true);
    }

    //DONE: on archive extraction also to take directories into consideration
    public static function extractUploadedArchive(&$file)
    {
        $archive = $file['name'];
        $ext = Files::getExtension($archive);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        switch ($ext) {
            case 'rar':
                $archive = rar_open($file['tmp_name']);
                if ($archive === false) {
                    return;
                }

                $file = array();
                $entries = rar_list($archive);
                foreach ($entries as $entry) {
                    if (!$entry->isDirectory()) { //we'll exclude empty directories
                        $tmp_name = tempnam(INCOMMING_FILES_DIR, 'cb_arch');
                        $entry->extract(INCOMMING_FILES_DIR, $tmp_name);
                        $file[] = array(
                            'dir' => dirname($entry->getName())
                            ,'name' => basename($entry->getName())
                            ,'type' => finfo_file($finfo, $tmp_name)
                            ,'tmp_name' => $tmp_name
                            ,'error' => 0
                            ,'size' => $entry->getUnpackedSize()
                        );
                    }
                }
                rar_close($archive);
                break;
            case 'zip':
                $zip = zip_open($file['tmp_name']);

                if (!is_resource($zip)) {
                    return;
                }
                $file = array();
                while ($zip_entry = zip_read($zip)) {
                    $name = zip_entry_name($zip_entry);
                    if (substr($name, -1) == '/') {
                        continue; //exclude directories
                    }
                    $tmp_name = tempnam(INCOMMING_FILES_DIR, 'cb_arch');
                    $size = zip_entry_filesize($zip_entry);
                    if (zip_entry_open($zip, $zip_entry, "r")) {
                        file_put_contents($tmp_name, zip_entry_read($zip_entry, $size));
                        zip_entry_close($zip_entry);
                    }
                    $file[] = array(
                        'dir' => dirname($name)
                        ,'name' => basename($name)
                        ,'type' => finfo_file($finfo, $tmp_name)
                        ,'tmp_name' => $tmp_name
                        ,'error' => 0
                        ,'size' => $size
                    );
                }
                zip_close($zip);
                break;
        }
    }

    public function moveUploadedFilesToIncomming(&$F)
    {
        foreach ($F as $fk => $f) {
            if (!empty($f['content_id'])) {
                //file content was not uploaded. Its content_id were sent as header param
                continue;
            }
            $new_name = INCOMMING_FILES_DIR.basename($f['tmp_name']);
            if ($f['tmp_name'] == $new_name) {
                continue;
            }
            if (false === move_uploaded_file($f['tmp_name'], $new_name)) {
                return false;
            }
            $F[$fk]['tmp_name'] = $new_name;
        }

        return true;
    }

    public function removeIncomingFiles($F)
    {
        foreach ($F as $f) {
            @unlink($f['tmp_name']);
        }

        return true;
    }

    public function getExistentFilenames($F, $pid)
    {
        //if no filenames already exists in target then the result will be an empty array
        $rez = array();
        foreach ($F as $fk => $f) {
            if ($this->fileExists($pid, $f['name'], @$f['dir'])) {
                $rez[] = $f;
            }
        }
        switch (sizeof($rez)) {
            case 0:
                break;
            case 1:
                //single match: retreive match info for content
                //(if matches with current version or to an older version)
                $existentFileId  = $this->getFileId($pid, $rez[0]['name'], @$rez[0]['dir']);
                $md5 = $this->getFileMD5($rez[0]);
                $res = DB\dbQuery(
                    'SELECT
                        (SELECT l'.USER_LANGUAGE_INDEX.'
                            FROM users_groups
                            WHERE id = f.cid) `user`
                        , f.cdate
                    FROM files f
                    JOIN files_content c ON f.content_id = c.id
                    AND c.md5 = $2
                    WHERE f.id = $1',
                    array(
                        $existentFileId
                        ,$md5
                    )
                ) or die(DB\dbQueryError());

                if ($r = $res->fetch_assoc()) {
                    $agoTime = Util\formatAgoTime($r['cdate']);
                    $rez[0]['msg'] = str_replace(
                        array('{timeAgo}', '{user}'),
                        array($agoTime,$r['user']),
                        L\FileContentsIdentical
                    );
                }
                $res->close();
                if (empty($rez[0]['msg'])) {
                    $res = DB\dbQuery(
                        'SELECT
                            (SELECT l'.USER_LANGUAGE_INDEX.'
                                FROM users_groups
                                WHERE id = f.cid) `user`
                            , f.cdate
                        FROM files_versions f
                        JOIN files_content c ON f.content_id = c.id
                        AND c.md5 = $2
                        WHERE f.file_id = $1',
                        array(
                            $existentFileId
                            ,$md5
                        )
                    ) or die(DB\dbQueryError());

                    if ($r = $res->fetch_assoc()) {
                        $agoTime = Util\formatAgoTime($r['cdate']);
                        $rez[0]['msg'] = str_replace(
                            array('{timeAgo}', '{user}'),
                            array($agoTime, $r['user']),
                            L\FileContentsIdenticalToAVersion
                        );

                    }
                }

                /* suggested new filename */
                $subdirId = $pid;
                if (!empty($rez[0]['dir'])) {
                    $subdirId = $this->getFileId($pid, '', $rez[0]['dir']);
                }
                $rez[0]['suggestedFilename'] = Objects::getAvailableName($subdirId, $rez[0]['name']);
                /* end of suggested new filename */
                break;
            default: // multiple files match

                break;
        }

        return $rez;
    }
    public function checkExistentContents($p)
    {
        foreach ($p as $k => $v) {
            $res = DB\dbQuery(
                'SELECT id FROM files_content WHERE `md5` = $1',
                $v
            ) or die(DB\dbQueryError());

            $p[$k] = ($r = $res->fetch_assoc()) ?  $r['id'] : null;
            $res->close();
        }

        return array('success' => true, 'data' => $p);
    }

    public function attachPostUploadInfo(&$FilesArray, &$result)
    {
        if (!is_array($FilesArray)) {
            return;
        }
        /* if a single file is uploaded then check if it has duplicates
        and inform user about available file duplicates */
        $msg = '';
        $prompt_to_open_file = false;
        switch (sizeof($FilesArray)) {
            case 0:
                break;
            case 1:
                reset($FilesArray);
                $f = current($FilesArray);
                $d = $this->getDuplicates($f['id']);
                $paths = array();
                if (sizeof($d['data']) > 0) {
                    foreach ($d['data'] as $dup) {
                        $paths[] = $dup['pathtext'];
                    }
                    $paths = array_unique($paths);
                    //msg: there are duplicates
                    $msg  = str_replace('{paths}', "\n<br />".implode('<br />', $paths), L\UploadedFileExistsInFolders);
                    $prompt_to_open_file = true;
                    $result['data']['id'] = $f['id'];
                }
                break;
            default:
                $filenames = array();
                foreach ($FilesArray as $f) {
                    $d = $this->getDuplicates($f['id']);
                    if (sizeof($d['data']) > 1) {
                        //msg: Following files have duplicates
                        $filenames[] = (empty($f['dir']) ? '': $f['dir'].DIRECTORY_SEPARATOR).$f['name'];
                    }
                }
                if (!emtpy($filenames)) {
                    $msg = L\FollowingFilesHaveDuplicates."\n<br />".implode('<br />', $filenames);
                }
                break;
        }
        if (!empty($msg)) {
            $result['msg'] = $msg;
        }
        if ($prompt_to_open_file) {
            $result['prompt_to_open'] = true;
        }
    }
    //checks if pid id exists in our tree or if filename exists under the pid.
    //$dir is an optional relative path under pid.
    public function getFileId($pid, $name = '', $dir = '')
    {
        $rez = null;
        /* check if pid exists /**/
        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE id = $1
                AND dstatus = 0',
            $pid
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['id'];
        } else {
            $rez = null;
        }
        $res->close();
        /* end of check if pid exists /**/

        if (empty($rez)) {
            return $rez;
        }

        if (!empty($name)) {
            $dir.= DIRECTORY_SEPARATOR.$name;
        }

        if (!empty($dir) && ($dir != '.')) {
            $dir = str_replace('\\', '/', $dir);
            $dir = explode('/', $dir);
            foreach ($dir as $dir_name) {
                if (empty($dir_name) || ($dir_name == '.')) {
                    continue;
                }
                $res = DB\dbQuery(
                    'SELECT id
                    FROM tree
                    WHERE pid = $1
                        AND name = $2
                        AND dstatus = 0',
                    array($rez, $dir_name)
                ) or die(DB\dbQueryError());

                if ($r = $res->fetch_assoc()) {
                    $rez = $r['id'];
                } else {
                    $rez = null;
                }
                $res->close();

                if (empty($rez)) {
                    return $rez;
                }
            }
        } else {
            $rez = null;
        }

        return $rez;
    }

    //checks if pid id exists in our tree or if filename exists under the pid.
    //$dir is an optional relative path under pid.
    public function fileExists($pid, $name = '', $dir = '')
    {
        $file_id = $this->getFileId($pid, $name, $dir);

        return !empty($file_id);
    }

    public function saveUploadParams($p)
    {
        file_put_contents(INCOMMING_FILES_DIR.$_SESSION['key'], serialize($p));
    }

    public function getUploadParams()
    {
        $rez = false;
        if (file_exists(INCOMMING_FILES_DIR.$_SESSION['key'])) {
            $rez = file_get_contents(INCOMMING_FILES_DIR.$_SESSION['key']);
            $rez = unserialize($rez);
        }

        return $rez;
    }

    /**
     * storeFiles move the files from incomming folder to file storage
     * @param array $p [ //upload params
     *       files property - array of uploaded files,
     *       response - response from user when asked about overwrite for single or many file
     * ]
     */
    public function storeFiles(&$p)
    {
        /* here we'll iterate all files and comparing the md5 with already contained
        files will upload only new contents to our store. Existent contents will be reused
        */
        foreach ($p['files'] as $fk => &$f) {
            if ($f['error'] == UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($f['error'] !== UPLOAD_ERR_OK) {
                continue;
            }

            @$f['date'] = Util\dateISOToMysql($p['date']);

            $this->storeContent($f);

            $pid = $p['pid'];
            if (!empty($f['dir'])) {
                $pid = $this->mkTreeDir($pid, $f['dir']);
            }

            $file_id = empty($p['id'])
                ? $this->getFileId($pid, $f['name'])
                : $p['id'];

            if (!empty($file_id)) {
                //newversion, replace, rename, autorename, cancel
                switch (@$p['response']) {
                    case 'newversion':
                        // case 'overwrite':
                        // case 'overwriteall':

                        //make the overwrite process: move record from files to files_versions,
                        //delete oldest version if exeeds versions limit for file type, add new record to files
                        $res = DB\dbQuery(
                            'INSERT INTO files_versions (
                                file_id
                                ,content_id
                                ,`date`
                                ,name
                                ,cid
                                ,uid
                                ,cdate
                                ,udate)
                                SELECT id
                                     , content_id
                                     , `date`
                                     , name
                                     , cid
                                     , uid
                                     , cdate
                                     , udate
                                FROM files
                                WHERE id = $1',
                            $file_id
                        ) or die(DB\dbQueryError());

                        break;
                    case 'replace':
                        /* TODO: only mark file as deleted but dont delte it */
                        DB\dbQuery('CALL p_delete_tree_node($1)', $file_id) or die(DB\dbQueryError());
                        $solr = new Solr\Client();
                        $solr->deleteByQuery('id:'.$file_id.' OR pids:'.$file_id);
                        break;
                    case 'rename':
                        $file_id = null;
                        $f['name'] = $p['newName']; //here is the new name
                        break;
                    case 'autorename':
                        $file_id = null;
                        $f['name'] = Objects::getAvailableName($pid, $f['name']);
                        break;
                }
            }
            $f['type'] = 5;//file
            // fireEvent('beforeNodeDbCreate', $f);
            DB\dbQuery(
                'INSERT INTO tree (
                    id
                    ,pid
                    ,`name`
                    ,`type`
                    ,cid
                    ,uid
                    ,cdate
                    ,udate
                    ,template_id)
                VALUES(
                    $1
                    ,$2
                    ,$3
                    ,5
                    ,$4
                    ,$4
                    ,CURRENT_TIMESTAMP
                    ,CURRENT_TIMESTAMP
                    ,$5)
                ON DUPLICATE KEY
                UPDATE id = last_insert_id($1)
                    ,pid = $2
                    ,`name` = $3
                    ,`type` = 5
                    ,cid = $4
                    ,uid = $4
                    ,cdate = CURRENT_TIMESTAMP
                    ,udate = CURRENT_TIMESTAMP
                    ,updated = (updated | 1)',
                array(
                    $file_id
                    ,$pid
                    ,$f['name']
                    ,$_SESSION['user']['id']
                    ,CONFIG\DEFAULT_FILE_TEMPLATE
                )
            ) or die(DB\dbQueryError());
            $file_id = DB\dbLastInsertId();

            DB\dbQuery(
                'INSERT INTO files (
                    id
                    ,content_id
                    ,`date`
                    ,`name`
                    ,`title`
                    ,cid
                    ,uid
                    ,cdate
                    ,udate)
                VALUES (
                    $1
                    ,$2
                    ,$3
                    ,$4
                    ,$5
                    ,$6
                    ,$6
                    ,CURRENT_TIMESTAMP
                    ,CURRENT_TIMESTAMP)
                ON duplicate KEY
                UPDATE content_id = $2
                    ,`date` = $3
                    ,`name` = $4
                    ,`title` = $5
                    ,cid = $6
                    ,uid = $6
                    ,cdate = CURRENT_TIMESTAMP
                    ,udate = CURRENT_TIMESTAMP',
                array(
                    $file_id
                    ,$p['files'][$fk]['content_id']
                    ,$p['files'][$fk]['date']
                    ,$f['name']
                    ,@$p['title']
                    ,$_SESSION['user']['id']
                )
            ) or die(DB\dbQueryError());

            $f['id'] = $file_id;
            // $p['files'][$fk]['id'] = $file_id;
            $this->updateFileProperties($f);
            // fireEvent('nodeDbCreate', $f);
        }

        return true;
    }

    public function updateFileProperties($p)
    {
        if (empty($p['id'])) {
            return false;
        }
        $p['title'] = strip_tags(@$p['title']);
        DB\dbQuery(
            'UPDATE files
            SET `date` = $2
            ,title = $3
            ,uid = $4
            ,udate = CURRENT_TIMESTAMP
            WHERE id = $1',
            array(
                $p['id']
                ,Util\dateISOToMysql($p['date'])
                ,@$p['title']
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        Objects::updateCaseUpdateInfo($p['id']);

        return true;
    }

    public function mkTreeDir($pid, $dir)
    {
        if (empty($dir) || ($dir == '.' )) {
            return $pid;
        }
        $path = str_replace('\\', '/', $dir);
        $path = explode('/', $path);
        foreach ($path as $dir) {
            if (empty($dir)) {
                continue;
            }
            $res = DB\dbQuery(
                'SELECT id
                FROM tree
                WHERE pid = $1
                    AND name = $2
                    AND dstatus = 0',
                array(
                    $pid
                    ,$dir
                )
            ) or die(DB\dbQueryError());

            if ($r = $res->fetch_assoc()) {
                $pid = $r['id'];
            } else {
                DB\dbQuery(
                    'INSERT INTO tree (pid, `name`, `type`, cid, uid, template_id)
                    VALUES($1
                         , $2
                         , 1
                         , $3
                         , $3
                         , $4)',
                    array(
                        $pid
                        ,$dir
                        ,$_SESSION['user']['id']
                        ,CONFIG\DEFAULT_FOLDER_TEMPLATE
                    )
                ) or die(DB\dbQueryError());
                $pid = DB\dbLastInsertId();
            }
            $res->close();
        }

        return $pid;
    }

    private function getFileMD5(&$file)
    {
        if (empty($file)) {
            return null;
        }

        return md5_file($file['tmp_name']).'s'.$file['size'];
    }
    public function storeContent(&$f, $filePath = false)
    {
        if ($filePath == false) {
            $filePath = FILES_DIR;
        }
        if (!empty($f['content_id']) && is_numeric($f['content_id'])) {
            return true; // content_id already defined
        }

        $f['content_id'] = null;
        if (!file_exists($f['tmp_name']) || ($f['size'] == 0)) {
            return false;
        }
        $md5 = $this->getFileMD5($f);
        $res = DB\dbQuery(
            'SELECT id, path FROM files_content WHERE md5 = $1',
            $md5
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            if (file_exists($filePath.$r['path'].'/'.$r['id'])) {
                $f['content_id'] = $r['id'];
            }
        }
        $res->close();

        if (!empty($f['content_id'])) {
            unlink($f['tmp_name']);

            return true;
        }

        /* file date will be used from file variable (date parametter) if specified.
        If not specified then system file_date will be used */
        $storage_subpath = empty($f['date']) ?
            date('Y/m/d', filemtime($f['tmp_name'])) :
            str_replace('-', '/', substr($f['date'], 0, 10));
        DB\dbQuery(
            'INSERT INTO files_content (`size`, `type`, `path`, `md5`)
            VALUES($1
                 , $2
                 , $3
                 , $4) ON duplicate KEY
            UPDATE id =last_insert_id(id)
            ,`size` = $1
            ,`type` = $2
            ,`path` = $3
            ,`md5` = $4',
            array(
                $f['size']
                ,$f['type']
                ,$storage_subpath
                ,$md5
            )
        ) or die(DB\dbQueryError());
        $f['content_id'] = DB\dbLastInsertId();
        @mkdir($filePath.$storage_subpath.'/', 0777, true);
        copy($f['tmp_name'], $filePath.$storage_subpath.'/'.$f['content_id']);
        @unlink($f['tmp_name']);

        return true;
    }

    public function removeContentId($id)
    {
    }

    public function getDuplicates($id)
    {
        $rez = array('success' => true, 'data' => array());
        if (!is_numeric($id)) {
            return $rez;
        }
        $res = DB\dbQuery(
            'SELECT
                 fd.id
                ,fd.cid
                ,fd.cdate
                ,case when(fd.name = f.name) THEN "" ELSE fd.name END `name`
                ,ti.pids `path`
                ,ti.path `pathtext`
            FROM files f
            JOIN files fd
                ON f.content_id = fd.content_id
                AND fd.id <> $1
            JOIN tree t
                ON fd.id = t.id
                and t.dstatus = 0
            JOIN tree_info ti
                ON t.id = ti.id
            WHERE f.id = $1',
            $id
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $r['path'] = str_replace(',', '/', $r['path']);
            $rez['data'][] = $r;
        }
        $res->close();

        return $rez;
    }
    public static function minimizeUploadedFile(&$file)
    {
        switch ($file['type']) {
            case 'application/pdf':
                $r = shell_exec('gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -dNOPAUSE -dQUIET -dBATCH -sOutputFile='.$file['tmp_name'].'_min '.$file['tmp_name']);
                if (file_exists($file['tmp_name'].'_min')) {
                    $file['tmp_name'] .='_min';
                    $file['size'] = filesize($file['tmp_name']);
                }
                break;
        }
    }

    public static function generatePreview($id, $version_id = false)
    {
        $rez = array();
        $file = array();
        $sql = 'SELECT f.id
                 , f.content_id
                 , f.name
                 , c.path
                 , c.`type`
                 , p.status
            FROM files f
            LEFT JOIN files_content c ON f.content_id = c.id
            LEFT JOIN file_previews p ON c.id = p.id
            WHERE f.id = $1
                AND c.size > 0';

        if (!empty($version_id)) {
            $sql = 'SELECT f.id
                     , f.content_id
                     , f.name
                     , c.path
                     , c.`type`
                     , p.status
                FROM files_versions f
                LEFT JOIN files_content c ON f.content_id = c.id
                LEFT JOIN file_previews p ON c.id = p.id
                WHERE f.file_id = $1
                    AND f.id = $2
                    AND c.size > 0';
        }

        $res = DB\dbQuery($sql, array($id, $version_id)) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $file = $r;
        }
        $res->close();

        if (empty($file)) {
            return array('html' => '');
        }
        if ($file['status'] > 0) {
            return array('processing' => true);
        }

        $ext = explode('.', $file['name']);
        $ext = array_pop($ext);
        $ext = strtolower($ext);
        $rez['ext'] = $ext;

        $rez['filename'] = $file['content_id'].'_.html';

        if (!file_exists(FILES_PREVIEW_DIR)) {
            @mkdir(FILES_PREVIEW_DIR, 0777, true);
        }

        $preview_filename = FILES_PREVIEW_DIR.$rez['filename'];

        $fn = FILES_DIR.$file['path'].DIRECTORY_SEPARATOR.$file['content_id'];
        $nfn = FILES_PREVIEW_DIR.$file['content_id'].'_.'.$ext;
        if (!file_exists($fn)) {
            return false;
        }
        switch ($ext) {
            case 'rtf':
            case 'doc':
            case 'xls':
            case 'csv':
            case 'ppt':
            case 'pps':
            case 'docx':
            case 'docm':
            case 'xlsx':
            case 'pptx':
            case 'odt':
                DB\dbQuery(
                    'INSERT INTO file_previews (id, `group`, status, filename, SIZE)
                        VALUES($1
                            ,\'office\'
                            ,1
                            ,NULL
                            ,0)
                        ON DUPLICATE KEY
                        UPDATE `group` = \'office\'
                            ,status =1
                            ,filename = NULL
                            ,SIZE = 0
                            ,cdate = CURRENT_TIMESTAMP',
                    $file['content_id']
                ) or die(DB\dbQueryError());

                if (file_exists($preview_filename)) {
                    Files::deletePreview($file['content_id']);
                }

                $cmd = 'php -f '.LIB_DIR.'PreviewExtractorOffice.php '.CORENAME.' > '.DEBUG_LOG.'_office &';
                if (isWindows()) {
                    $cmd = 'start /D "'.LIB_DIR.'" php -f PreviewExtractorOffice.php '.CORENAME;
                }
                pclose(popen($cmd, "r"));

                return array('processing' => true);
                break;
            case 'xml':
            case 'htm':
            case 'html':
            case 'dhtml':
            case 'xhtml':
                //file_put_contents( $preview_filename, Files::purify(file_get_contents($fn)) );
                require_once LIB_DIR.'PreviewExtractor.php';
                $content = file_get_contents($fn);
                $pe = new PreviewExtractor();
                $content = $pe->purify(
                    $content,
                    array(
                        'URI.Base' => '/preview/'
                        ,'URI.MakeAbsolute' => true
                    )
                );
                file_put_contents($preview_filename, $content);
                //copy($fn, $preview_filename);
                break;
            case 'txt':
            case 'css':
            case 'js':
            case 'json':
            case 'php':
            case 'bat':
            case 'ini':
            case 'sys':
            case 'sql':
                file_put_contents(
                    $preview_filename,
                    '<pre>'.Util\adjustTextForDisplay(file_get_contents($fn)).'<pre>'
                );
                break;
            case 'pdf':
                $html = 'PDF'; //Ext panel - PreviewPanel view
                if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) { //full browser window view
                    require_once(MINIFY_PATH.'utils.php');

                    $html = '<html><head><title>'.$file['name'].'</title>
                            <script type="text/javascript" src="'.Minify_getUri('js_pdf').'"></script>
                            <script type="text/javascript">
                                  window.onload = function (){
                                    var success = new PDFObject({ url: "/download.php?pw=&amp;id='.$file['id'].'" }).embed();
                                  };
                            </script>
                          </head>
                      <body>
                        <p>It appears you don\'t have Adobe Reader or PDF support in this web browser.
                            <a href="/download.php?id='.$file['id'].'">Click here to download the PDF</a></p>
                    </body>
                    </html>';
                }

                return array('html' => $html);
                break;
            case 'tif':
            case 'tiff':
                $image = new \Imagick($fn);
                $image->setImageFormat('png');
                $image->writeImage(FILES_PREVIEW_DIR.$file['content_id'].'_.png');
                file_put_contents(
                    $preview_filename,
                    '<img src="/preview/'.$file['content_id'].
                    '_.png" style="max-width:90%;margin: auto" />'
                );
                break;
            default:
                if (substr($file['type'], 0, 5) == 'image') {
                    file_put_contents(
                        $preview_filename,
                        '<div style="padding: 5px 10px"><img src="/download.php?id='.
                        $file['id'].
                        (empty($version_id) ? '' : '&v='.$version_id).
                        '" style="max-width:90%;margin: auto"></div>'
                    );
                }
        }

        return $rez;
    }
    public static function getFilesBlockForPreview($pid)
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT id, name, size, cdate
            FROM tree
            WHERE pid = $1
                AND `type` = 5
                AND dstatus = 0
            ORDER BY cdate DESC',
            $pid
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $rez[] = '<li class="icon-padding file-unknown file-'.
                Files::getExtension($r['name']).
                '"><a name="file" href="#" nid="'.$r['id'].'">'.$r['name'].
                '</a><p class="cG">'.Util\formatFileSize($r['size']).', '.
                Util\formatAgoTime($r['cdate']).'</p>';
        }
        $res->close();
        $rez = empty($rez) ? '' : '<ul class="obj-files">'.implode('', $rez).'</ul>';

        return $rez;
    }

    public static function deletePreview($id)
    {
        if (isWindows()) {
            $cmd = 'del '.FILES_PREVIEW_DIR.$id.'_*';
        } else {
            $cmd = 'find '.FILES_PREVIEW_DIR.' -type f -name '.$id.'_* -print | xargs rm';
        }
        exec($cmd);
    }

    public static function getSolrData(&$objectRecord)
    {
        return; //TODO: refactor according to objectsRecord parametter and solr content search refactor
        $rez = array();
        //,parsed_content `content`'.
        $filesPath = '';
        if (defined('FILES_DIR')) {
            $filesPath = FILES_DIR;
        } else {
            global $core;
            $filesPath = FILES_DIR.$core['name'].DIRECTORY_SEPARATOR;
        }
        $res = DB\dbQuery(
            'SELECT f.id
            ,c.type
            ,c.size
            ,c.pages
            ,c.path
            ,f.name
            ,f.title
            ,f.cid
            ,f.content_id
            ,(select count(*) from files_versions where file_id = f.id) `versions`
            FROM files f left join files_content c on f.content_id = c.id where f.id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez['size'] = $r['size'];
            $rez['versions'] = intval($r['versions']);
            $content = $filesPath.$r['path'].DIRECTORY_SEPARATOR.$r['content_id'].'.gz';
            if (file_exists($content)) {
                $content =   file_get_contents($content);
                $content = gzuncompress($content);
            } else {
                $content = '';
            }
            $rez['content'] = Util\coalesce($r['title'], '')."\n".
            Util\coalesce($r['type'], '')."\n".
            Util\coalesce($content, '');
        }
        $res->close();

        return $rez;
    }

    public static function getBulkSolrData(&$objectRecords)
    {
        return; //TODO: refactor according to objectsRecords parametter and solr content search refactor
    }

    /* versions */
    public function restoreVersion($id)
    {
        $rez = array(
            'success' => true
            ,'data' => array(
                'id' => 0
                ,'pid' => 0
            )
        );
        $file_id = 0;

        $res = DB\dbQuery(
            'SELECT file_id
            FROM files_versions
            WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $file_id = $r['file_id'];
            $rez['data']['id'] = $file_id;
        }
        $res->close();

        $res = DB\dbQuery(
            'SELECT pid
            FROM tree
            WHERE id = $1',
            $file_id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez['data']['pid'] = $r['pid'];
        }
        $res->close();

        $res = DB\dbQuery(
            'INSERT INTO files_versions (
                file_id
                ,content_id
                ,`date`
                ,name
                ,cid
                ,uid
                ,cdate
                ,udate)
            SELECT id
                ,content_id
                ,`date`
                ,name
                ,cid
                ,uid
                ,cdate
                ,udate
            FROM files
            WHERE id = $1',
            $file_id
        ) or die(DB\dbQueryError());

        DB\dbQuery(
            'INSERT INTO files (
                id
                ,content_id
                ,`date`
                ,`name`
                ,cid
                ,uid
                ,cdate
                ,udate)
            SELECT file_id
                ,content_id
                ,`date`
                ,`name`
                ,cid
                ,$2
                ,CURRENT_TIMESTAMP
                ,CURRENT_TIMESTAMP
            FROM files_versions v
            WHERE id = $1 ON duplicate KEY
                UPDATE content_id = v.content_id
                    ,`date` = v.date
                    ,`name` = v.name
                    ,cid = v.cid
                    ,uid = $2
                    ,cdate = CURRENT_TIMESTAMP
                    ,udate = CURRENT_TIMESTAMP',
            array(
                $id
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        Objects::updateCaseUpdateInfo($id);

        Solr\Client::runCron();

        return $rez;
    }
    public function deleteVersion($id)
    {
        $rez = array('success' => true, 'id' => $id);
        $content_id = 0;
        $res = DB\dbQuery(
            'SELECT content_id
            FROM files_versions
            WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $content_id = $r['content_id'];
        }
        $res->close();

        DB\dbQuery(
            'DELETE
            FROM files_versions
            WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        $this->removeContentId($content_id);

        DB\dbQuery(
            'UPDATE tree
            SET `updated` = (updated | 1)
            WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        Objects::updateCaseUpdateInfo($id);

        Solr\Client::runCron();

        return $rez;
    }
    /* end of versions */
    public function merge($ids)
    {
        if (!is_array($ids)) {
            return array('success' => false);
        }
        $ids = array_filter($ids, 'is_numeric');
        if (sizeof($ids) < 2) {
            return array('success' => false);
        }

        $to_id = null;
        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE id IN ('.implode(', ', $ids).')
            ORDER BY udate DESC, id DESC'
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $to_id = $r['id'];
        }
        $res->close();

        DB\dbQuery(
            'UPDATE files_versions
            SET file_id = $1
            WHERE file_id IN ('.implode(', ', $ids).')',
            $to_id
        ) or die(DB\dbQueryError());

        $res = DB\dbQuery(
            'INSERT INTO files_versions (file_id, content_id, `date`, name, cid, uid, cdate, udate)
                SELECT $1
                     , content_id
                     , `date`
                     , name
                     , cid
                     , uid
                     , cdate
                     , udate
                FROM files
                WHERE id <> $1
                    AND id in('.implode(',', $ids).')',
            $to_id
        ) or die(DB\dbQueryError());

        DB\dbQuery(
            'UPDATE tree
            SET did = $2
                    , dstatus = 1
                    , updated = (updated | 1)
            WHERE id <> $1
                AND id IN ('.implode(', ', $ids).')',
            array(
                $to_id
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        DB\dbQuery(
            'UPDATE tree
            SET updated = (updated | 1)
            WHERE id = $1',
            $to_id
        ) or die(DB\dbQueryError());

        $ids = array_diff($ids, array($to_id));

        Objects::updateCaseUpdateInfo($id);

        Solr\Client::runCron();

        return array('success' => true, 'rez' => $ids);
    }

    public function getRAROriginalSize($filename)
    {
        $size = 0;
        $resource = rar_open($filename);
        if ($resource === false) {
            return $size;
        }

        $entries = rar_list($resource);
        foreach ($entries as $entry) {
            //we'll exclude empty directories
            if (!$entry->isDirectory()) {
                $size += $entry->getUnpackedSize();
            }
        }
        rar_close($resource);

        return $size;
    }

    public function getZIPOriginalSize($filename)
    {
        $size = 0;
        $resource = zip_open($filename);
        if (!is_resource($resource)) {
            return $size;
        }
        //

        while ($dir_resource = zip_read($resource)) {
            $size += zip_entry_filesize($dir_resource);
        }
        zip_close($resource);

        return $size;
    }

    public static function getExtension($filename)
    {
        $ext = explode('.', $filename);
        if (sizeof($ext) <2) {
            return '';
        }
        $ext = array_pop($ext);
        $ext = trim($ext);

        return mb_strtolower($ext);
    }

    public static function getIcon($filename)
    {
        if (empty($filename)) {
            return 'file-unknown';
        }

        return 'file- file-'.Files::getExtension($filename);
    }

    public static function getIconFileName($filename)
    {
        $ext = Files::getExtension($filename);
        switch ($ext) {
            case 'docx':
            case 'rtf':
                $ext = 'doc';
                break;
            case 'pptx':
                $ext = 'ppt';
                break;
            case 'txt':
                $ext = 'text';
                break;
            case 'html':
                $ext = 'htm';
                break;
            case 'rm':
                $ext = 'mp3';
                break;
            case 'gif':
            case 'jpg':
            case 'jpeg':
            case 'tif':
            case 'bmp':
            case 'png':
                $ext = 'img';
                break;
        }
        $filename = $ext.'.png';
        if (file_exists(DOC_ROOT.'css/i/ext/'.$filename)) {
            return $filename;
        } else {
            return '.png';
        }
    }

    public static function setMFVC($configurationString)
    {
        /* storing max file versions count (mfvc)*/
        //*:1;doc,docx,xls,xlsx,pdf:5;
        //default is no versions if nothing specified in config
        $GLOBALS['mfvc'] = array('*' => 0);

        $v = defined('CB\\CONFIG\\MAX_FILES_VERSION_COUNT') ?
            CONFIG\MAX_FILES_VERSION_COUNT : null;
        if (!empty($v)) {
            $v = explode(';', $v);
            foreach ($v as $vc) {
                $vc = explode(':', $vc);
                if (sizeof($vc) == 2) {
                    $ext = trim($vc[0]);
                    $count = trim($vc[1]);
                    if (is_numeric($count)) {
                        $ext = explode(',', $ext);
                        foreach ($ext as $e) {
                            $e = trim($e);
                            $e = mb_strtolower($e);
                            $GLOBALS['mfvc'][$e] = $count;
                        }
                    }
                }
            }
        }
        /* end of storing max file versions configuration fr core in session */
    }

    //get Max File Version Count for an extension
    public static function getMFVC($filename)
    {
        $ext = Files::getExtension($filename) || mb_strtolower($filename);
        $ext = trim($ext);
        $rez = 0;
        if (empty($GLOBALS['mfvc'])) {
            return $rez;
        }
        $ext = mb_strtolower($ext);
        if (isset($GLOBALS['mfvc'][$ext])) {
            return $GLOBALS['mfvc'][$ext];
        }
        if (isset($GLOBALS['mfvc']['*'])) {
            return $GLOBALS['mfvc']['*'];
        }

        return $rez;
    }
}
