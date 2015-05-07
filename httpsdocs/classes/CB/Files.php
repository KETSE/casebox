<?php
namespace CB;

use CB\Util;

class Files
{
    public static function getProperties($id)
    {
        $rez = array(
            'success' => true
            ,'data' => array()
        );

        if (!is_numeric($id)) {
            return $rez;
        }

        $rez['menu'] = Browser\CreateMenu::getMenuForPath($id);

        $file = new Objects\File($id);
        $rez['data'] = $file->load();

        $d = &$rez['data'];
        $d['path'] = str_replace(',', '/', $d['path']);
        $a = explode('.', $d['name']);

        $t = strtotime($d['cdate']);
        $dateFormat = getOption('long_date_format');
        $timeFormat = getOption('time_format');

        $d['ago_date'] = date($dateFormat, $t)
            . ' ' . L\get('at') . ' ' .
            date($timeFormat, $t);

        $d['ago_date'] = Util\translateMonths($d['ago_date']);
        $d['ago_text'] = Util\formatAgoTime($d['cdate']);

        if (!empty($d['versions'])) {
            foreach ($d['versions'] as &$r) {
                $r['template_id'] = $rez['data']['template_id'];
                $t = strtotime($r['cdate']);
                $r['ago_date'] = date($dateFormat, $t)
                    . ' ' . L\get('at') . ' ' .
                    date($timeFormat, $t);

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
            throw new \Exception(L\get('Access_denied'));
        }
        $file = new Objects\File($p['id']);
        $file->setData($p);
        $file->save();

        return array('success' => true);
    }

    public static function getContent($id)
    {
        $rez = array('success' => true, 'data' => null);

        $file = new Objects\File($id);

        $data = $file->load();

        $contentFile = Config::get('files_dir') . @$data['content_path'] . '/'.@$data['content_id'];

        if (file_exists($contentFile) && !is_dir($contentFile)) {
            $rez['data'] = Util\toUTF8String(file_get_contents($contentFile));
        } else {
            \CB\debug('Error accessing file ('.$id.'). Its content (id: '.@$data['content_id'].') doesnt exist on the disk.');

            return array('success' => false);
        }

        return $rez;
    }

    public function saveContent($p)
    {
        if (!Security::canWrite($p['id'])) {
            throw new \Exception(L\get('Access_denied'));
        }

        $this->saveCurrentVersion($p['id']);

        $file = new Objects\File($p['id']);
        $data = $file->load();

        $content = array(
            'tmp_name' => tempnam(Config::get('incomming_files_dir'), 'cbup')
            ,'date' => date('Y-m-d')
            ,'name' => $data['name']
            ,'type' => $data['type']
        );
        file_put_contents($content['tmp_name'], $p['data']);
        $content['size'] = filesize($content['tmp_name']);

        $this->storeContent($content);

        $data['content_id'] = $content['content_id'];
        $file->update($data);
        // $contentFile = Config::get('files_dir') . $data['content_path'] . '/'.$data['content_id'];

        // file_put_contents($contentFile, $p['data']);
        return array('success' => true);
    }

    /**
     * download files
     *
     * outputs file content and set corresponding header params
     *
     * @param  int  $id file id
     * @return void
     */
    public static function download($id, $versionId = null, $asAttachment = true, $forUseId = false)
    {

        $sql = empty($versionId)
            ? 'SELECT f.id
                ,f.content_id
                ,c.path
                ,f.name
                ,c.`type`
                ,c.size
            FROM files f
            LEFT JOIN files_content c ON f.content_id = c.id
            WHERE f.id = $1'

            : 'SELECT f.file_id `id`
                ,f.id `version_id`
                ,f.content_id
                ,c.path
                ,f.name
                ,c.`type`
                ,c.size
            FROM files_versions f
            LEFT JOIN files_content c ON f.content_id = c.id
            WHERE f.id = $1';

        $res = DB\dbQuery($sql, Util\coalesce($versionId, $id)) or die( DB\dbQueryError() );

        if ($r = $res->fetch_assoc()) {
            //check if can download file
            if (!Security::canDownload($r['id'], $forUseId)) {
                throw new \Exception(L\get('Access_denied'));
            }

            header('Content-Description: File Transfer');
            header('Content-Type: '.$r['type'].'; charset=UTF-8');
            if ($asAttachment || ($r['type'] !== 'application/pdf')) {
                //purify filename for cases when we have a wrong filename in the system already
                header('Content-Disposition: attachment; filename="'.Purify::filename($r['name']).'"');
            }

            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: '.$r['size']);
            readfile(Config::get('files_dir') . $r['path'] . DIRECTORY_SEPARATOR . $r['content_id']);
        } else {
            throw new \Exception(L\get('Object_not_found'));
        }
        $res->close();
    }

    /**
     * save current file version into versions table
     * and delete versions exceeding mfvc
     * @param  int  $id   file id
     * @param  int  $mfvc max file version count
     * @return void
     */
    protected function saveCurrentVersion($id, $mfvc = false)
    {
        if ($mfvc === false) {
            $mfvc = $this->getMFVC(Objects::getName($id));
        }

        if (empty($mfvc)) {
            return false;
        }

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
            $id
        ) or die(DB\dbQueryError());

        //detect versions exceeding mfvc and delete them
        $deleteVersionIds = array();
        $res = DB\dbQuery(
            'SELECT id
            FROM files_versions
            WHERE file_id = $1
            ORDER BY id DESC
            LIMIT ' . $mfvc . ', 10',
            $id
        ) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $deleteVersionIds[] = $r['id'];
        }
        $res->close();

        if (!empty($deleteVersionIds)) {
            DB\dbQuery(
                'DELETE
                FROM files_versions
                WHERE id in (' . implode(',', $deleteVersionIds) . ')'
            ) or die(DB\dbQueryError());
        }

        return true;
    }

    //DONE: on archive extraction also to take directories into consideration
    public static function extractUploadedArchive(&$file)
    {
        $archive = $file['name'];
        $ext = Files::getExtension($archive);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $incommingFilesDir = Config::get('incomming_files_dir');

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
                        $tmp_name = tempnam($incommingFilesDir, 'cb_arch');
                        $entry->extract($incommingFilesDir, $tmp_name);
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
                    $tmp_name = tempnam($incommingFilesDir, 'cb_arch');
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
            $new_name = Config::get('incomming_files_dir').basename($f['tmp_name']);
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
        $userLanguageIndex = Config::get('user_language_index');

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
                $existentFileId = $this->getFileId($pid, $rez[0]['name'], @$rez[0]['dir']);
                $rez[0]['existentFileId'] = $existentFileId;

                $md5 = $this->getFileMD5($rez[0]);
                $res = DB\dbQuery(
                    'SELECT
                        f.cid
                        ,f.cdate
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
                    $r['user'] = User::getDisplayName($r['cid']);

                    $agoTime = Util\formatAgoTime($r['cdate']);

                    $rez[0]['msg'] = str_replace(
                        array('{timeAgo}', '{user}'),
                        array($agoTime,$r['user']),
                        L\get('FileContentsIdentical')
                    );
                }
                $res->close();

                if (empty($rez[0]['msg'])) {
                    $res = DB\dbQuery(
                        'SELECT
                            f.cid
                            ,f.cdate
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
                        $r['user'] = User::getDisplayName($r['cid']);

                        $agoTime = Util\formatAgoTime($r['cdate']);

                        $rez[0]['msg'] = str_replace(
                            array('{timeAgo}', '{user}'),
                            array($agoTime, $r['user']),
                            L\get('FileContentsIdenticalToAVersion')
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

        $filesDir = Config::get('files_dir');

        foreach ($p as $k => $v) {
            $res = DB\dbQuery(
                'SELECT id, `path`
                FROM files_content
                WHERE `md5` = $1',
                $v
            ) or die(DB\dbQueryError());

            if ($r = $res->fetch_assoc()) {
                //give affirmative result only if the correspondig file content exists
                $p[$k] = file_exists($filesDir.$r['path'].DIRECTORY_SEPARATOR.$r['id'])
                    ? $r['id']
                    : null;
            } else {
                unset($p[$k]);
            }
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
                    $msg  = str_replace('{paths}', "\n<br />".implode('<br />', $paths), L\get('UploadedFileExistsInFolders'));
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
                    $msg = L\get('FollowingFilesHaveDuplicates')."\n<br />".implode('<br />', $filenames);
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
    public static function getFileId($pid, $name = '', $dir = '')
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
    public static function fileExists($pid, $name = '', $dir = '')
    {
        $file_id = static::getFileId($pid, $name, $dir);

        return !empty($file_id);
    }

    /**
     * get file size
     * @param  int $id
     * @return int
     */
    public static function getSize($id)
    {
        $rez = 0;
        $res = DB\dbQuery(
            'SELECT fc.size
            FROM files f
            JOIN files_content fc
                ON f.content_id = fc.id
            WHERE f.id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = intval($r['size']);
        }
        $res->close();

        return $rez;
    }


    public function saveUploadParams($p)
    {
        file_put_contents(Config::get('incomming_files_dir') . $_SESSION['key'], serialize($p));
    }

    public function getUploadParams()
    {
        $rez = false;
        $incommingFilesDir = Config::get('incomming_files_dir');

        if (file_exists($incommingFilesDir . $_SESSION['key'])) {
            $rez = file_get_contents($incommingFilesDir . $_SESSION['key']);
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

            //apply general properties from $p to $f (file) variable
            foreach ($p as $k => $v) {
                if (in_array($k, array('id', 'pid', 'draftPid', 'name', 'title', 'content_id', 'template_id', 'cid', 'oid', 'data'))) {
                    $f[$k] = $v;
                }
            }

            @$f['date'] = Util\dateISOToMysql($p['date']);

            if (empty($f['template_id'])) {
                $f['template_id'] = Config::get('default_file_template');
            }

            $this->storeContent($f);

            $pid = $p['pid'];
            if (!empty($f['dir'])) {
                $pid = $this->mkTreeDir($pid, $f['dir']);
                $f['pid'] = $pid;
            }

            $fileId = empty($p['id'])
                ? $this->getFileId($pid, $f['name'])
                : intval($p['id']);

            if (!empty($fileId)) {
                //newversion, replace, rename, autorename, cancel
                switch (@$p['response']) {
                    case 'newversion':
                        // case 'overwrite':
                        // case 'overwriteall':

                        $this->saveCurrentVersion($fileId);
                        break;
                    case 'replace':
                        /* TODO: only mark file as deleted but dont delte it
                            Note: we cant leae the previous file record if we have a given id for file

                        */
                        DB\dbQuery('CALL p_delete_tree_node($1)', $fileId) or die(DB\dbQueryError());
                        $solr = new Solr\Client();
                        $solr->deleteByQuery('id:' . $fileId . ' OR pids: ' . $fileId);
                        break;
                    case 'rename':
                        $fileId = null;
                        $f['name'] = $p['newName']; //here is the new name
                        break;
                    case 'autorename':
                        $fileId = null;
                        $f['name'] = Objects::getAvailableName($pid, $f['name']);
                        break;
                }
            }
            $f['type'] = 5;//file

            //save file
            $fileObject = new Objects\File();
            if (!empty($fileId)) {
                $f['id'] = $fileId;
                if (@$p['response'] == 'replace') {
                    $fileObject->create($f);
                } else {
                    $fileObject->update($f);
                }
            } else {
                $f['id'] = $fileObject->create($f);
            }

            $this->updateFileProperties($f);
        }

        return true;
    }

    public function updateFileProperties($p)
    {
        if (empty($p['id'])) {
            return array('success' => false, 'msg' => L\get('Wrong_input_data'));
        }

        if (!Security::canWrite($p['id'])) {
            return array('success' => false, 'msg' => L\get('Access_denied'));
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

        return array('success' => true);
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
                        ,Config::get('default_folder_template')
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
            $filePath = Config::get('files_dir');
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
        $date = false;
        if (!empty($f['date'])) {
            $date = strtotime($f['date']);
        }

        $storage_subpath = ($date === false)
            ? date('Y/m/d', filemtime($f['tmp_name']))
            : date('Y/m/d', $date);

        DB\dbQuery(
            'INSERT INTO files_content (`size`, `type`, `path`, `md5`)
            VALUES($1
                 , $2
                 , $3
                 , $4) ON duplicate KEY
            UPDATE id = last_insert_id(id)
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

        if (copy($f['tmp_name'], $filePath.$storage_subpath.'/'.$f['content_id']) !== true) {
            throw new \Exception("Error copying file to destination folder, possible permission problems.", 1);
        }

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
        $coreName = Config::get('core_name');
        $coreUrl = Config::get('core_url');

        $filesDir = Config::get('files_dir');
        $filesPreviewDir = Config::get('files_preview_dir');

        $sql = 'SELECT f.id
                ,f.content_id
                ,f.name
                ,c.path
                ,c.`type`
                ,p.status
            FROM files f
            LEFT JOIN files_content c ON f.content_id = c.id
            LEFT JOIN file_previews p ON c.id = p.id
            WHERE f.id = $1
                AND c.size > 0';

        if (!empty($version_id)) {
            $sql = 'SELECT $1 `id`
                    ,f.id `version_id`
                    ,f.content_id
                    ,f.name
                    ,c.path
                    ,c.`type`
                    ,p.status
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
            \CB\debug('Error accessing file preview ('.$id.'). Record not found in DB.');

            return array('html' => '');
        }

        switch ($file['status']) {
            case 1:
            case 2:
                return array(
                    'processing' => true
                );

            case 3:
                return array(
                    'html' => L\get('ErrorCreatingPreview')
                );

        }

        $ext = explode('.', $file['name']);
        $ext = array_pop($ext);
        $ext = strtolower($ext);
        $rez['ext'] = $ext;

        $rez['filename'] = $file['content_id'].'_.html';

        if (!file_exists($filesPreviewDir)) {
            @mkdir($filesPreviewDir, 0777, true);
        }

        $preview_filename = $filesPreviewDir.$rez['filename'];

        $fn = $filesDir.$file['path'].DIRECTORY_SEPARATOR.$file['content_id'];
        $nfn = $filesPreviewDir.$file['content_id'].'_.'.$ext;

        if (!file_exists($fn)) {
            \CB\debug('Error accessing file preview ('.$id.'). Its content (id: '.@$file['content_id'].') doesnt exist on the disk.');

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

                $cmd = 'php -f '.LIB_DIR.'PreviewExtractorOffice.php -- -c '.$coreName.' > '.Config::get('debug_log').'_office &';
                if (IS_WINDOWS) {
                    $cmd = 'start /D "'.LIB_DIR.'" php -f PreviewExtractorOffice.php -- -c '.$coreName;
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
                        'URI.Base' => '/' . $coreName . '/'
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
                    '<pre>' .Util\adjustTextForDisplay(file_get_contents($fn)).'<pre>'
                );
                break;
            case 'pdf':
                $html = 'PDF'; //Ext panel - PreviewPanel view
                if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) { //full browser window view
                    $url = $coreUrl . 'download/' . $file['id'] . '/';
                    $html = '
                        <object data="' . $url . '" type="application/pdf" width="100%" height="100%">
                            It appears you don\'t have Adobe Reader or PDF support in this web browser.
                            <a href="' . $url . '">Click here to download the file</a>
                            <embed src="' . $url . '" type="application/pdf" />
                        </object>';
                }

                return array('html' => $html);
                break;
            case 'tif':
            case 'tiff':
            case 'svg':
                $convertedImage = $filesPreviewDir.$file['content_id'].'_.png';
                if (!file_exists($convertedImage)) {
                    try {
                        $image = new \Imagick($fn);
                        $image->setImageFormat('png');
                        $image->writeImage($convertedImage);
                    } catch (\Exception $e) {
                        return $rez;
                    }
                }

                file_put_contents(
                    $preview_filename,
                    '<img src="/' . $coreName . '/view/'.$file['content_id'].
                    '_.png" class="fit-img" style="margin: auto" />'
                );
                break;

            default:
                if ((substr($file['type'], 0, 5) == 'image') && (substr($file['type'], 0, 9) !== 'image/svg')) {
                    file_put_contents(
                        $preview_filename,
                        '<div style="padding: 5px 10px"><img src="/'.$coreName.'/download/'.
                        $file['id'] .
                        (empty($version_id) ? '' : '/' . $version_id) .
                        '/" class="fit-img" style="margin: auto"></div>'
                    );
                }
        }

        return $rez;
    }

    public static function deletePreview($id)
    {
        $filesPreviewDir = Config::get('files_preview_dir');

        if (IS_WINDOWS) {
            $cmd = 'del '.$filesPreviewDir.$id.'_*';
        } else {
            $cmd = 'find '.$filesPreviewDir.' -type f -name '.$id.'_* -print | xargs rm';
        }
        exec($cmd);
    }

    public static function getSolrData(&$objectRecord)
    {
        //make standart analysis of task object
        Objects::getSolrData($objectRecord);

        $filesPath = Config::get('files_dir');

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
            FROM files f
            LEFT JOIN files_content c
                ON f.content_id = c.id
            WHERE f.id = $1',
            $objectRecord['id']
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            // $objectRecord['type'] = $r['type'];
            $objectRecord['size'] = $r['size'];
            $objectRecord['versions'] = intval($r['versions']);

            $content = $filesPath.$r['path'].DIRECTORY_SEPARATOR.$r['content_id'].'.gz';
            if (file_exists($content)) {
                $content = file_get_contents($content);
                $content = gzuncompress($content);
            } else {
                $content = '';
            }
            $objectRecord['content'] =
                Util\coalesce($r['title'], '')."\n".
                Util\coalesce($r['type'], '')."\n".
                (empty($objectRecord['content']) ? '' : $objectRecord['content'] . "\n").
                Util\coalesce($content, '');
        }
        $res->close();
    }

    public static function getBulkSolrData(&$objectRecords)
    {
        foreach ($objectRecords as $id => &$objectRecord) {
            if (@$objectRecord['template_type'] == 'file') {
                static::getSolrData($objectRecord);
            }
        }
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

        //detect file id
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

        //get restored version data
        $versionData = array();
        $res = DB\dbQuery(
            'SELECT *
            FROM files_versions v
            WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $versionData = $r;
        }
        $res->close();

        $this->saveCurrentVersion($file_id);

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
                VALUES (
                    $1, $2, $3, $4, $5, $6, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                )
            ON DUPLICATE KEY UPDATE
                content_id = $2
                ,`date` = $3
                ,`name` = $4
                ,cid = $5
                ,uid = $6
                ,cdate = CURRENT_TIMESTAMP
                ,udate = CURRENT_TIMESTAMP',
            array(
                $versionData['file_id']
                ,$versionData['content_id']
                ,$versionData['date']
                ,$versionData['name']
                ,$versionData['cid']
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

    /**
     * storing max file versions count (mfvc)
     *     *:1;doc,docx,xls,xlsx,pdf:5;
     *     default is no versions if nothing specified in config
     */
    public static function setMFVC($configurationString)
    {
        $rez = array('*' => 0);

        if (!empty($configurationString)) {
            $v = explode(';', $configurationString);
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
                            $rez[$e] = $count;
                        }
                    }
                }
            }
        }

        Config::setEnvVar('mfvc', $rez);

        return $rez;
    }

    //get Max File Version Count for an extension
    public static function getMFVC($filename)
    {
        $ext = Files::getExtension($filename);
        if (empty($ext)) {
            $ext = mb_strtolower($filename);
        }

        $ext = trim($ext);

        $rez = 0;

        $mfvc = Config::get('mfvc');

        if (empty($mfvc)) {
            return $rez;
        }

        $ext = mb_strtolower($ext);

        if (isset($mfvc[$ext])) {
            return $mfvc[$ext];
        }
        if (isset($mfvc['*'])) {
            return $mfvc['*'];
        }

        return $rez;
    }
}
