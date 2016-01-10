<?php
namespace CB;

use CB\Util;
use CB\DataModel as DM;

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

        $r = empty($versionId)
            ? DM\Files::read($id)
            : DM\FilesVersions::read($versionId);

        if (!empty($r)) {
            $content = DM\FilesContent::read($r['content_id']);

            //check if can download file
            if (!Security::canDownload($r['id'], $forUseId)) {
                throw new \Exception(L\get('Access_denied'));
            }

            header('Content-Description: File Transfer');
            header('Content-Type: '.$content['type'].'; charset=UTF-8');
            if ($asAttachment || ($content['type'] !== 'application/pdf')) {
                //purify filename for cases when we have a wrong filename in the system already
                header('Content-Disposition: attachment; filename="'.Purify::filename($r['name']).'"');
            }

            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: '.$content['size']);
            readfile(Config::get('files_dir') . $content['path'] . DIRECTORY_SEPARATOR . $content['id']);

        } else {
            throw new \Exception(L\get('Object_not_found'));
        }
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

        $data = DM\Files::read($id);

        $data['file_id'] = $data['id'];
        unset($data['id']);

        DM\FilesVersions::create($data);

        //detect versions exceeding mfvc and delete them
        if ($dIds = DM\FilesVersions::getOldestIds($id, $mfvc)) {
            DM\FilesVersions::delete($dIds);
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

        foreach ($F as $f) {
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
                $file = DM\Files::read($existentFileId);
                $content = DM\FilesContent::read($file['content_id']);

                $md5 = $this->getFileMD5($rez[0]);

                $data = array();

                if ($md5 == $content['md5']) {
                    $data = $file;
                    $data['text'] = L\get('FileContentsIdentical');
                }

                if (empty($rez[0]['msg'])) {
                    $version = DM\FilesVersions::getVersionByMD5($existentFileId, $md5);

                    if (!empty($version)) {
                        $data = $version;
                        $data['text'] = L\get('FileContentsIdenticalToAVersion');
                    }
                }

                if (!empty($data)) {
                    $user = User::getDisplayName($data['cid']);

                    $agoTime = Util\formatAgoTime($data['cdate']);

                    $rez[0]['msg'] = str_replace(
                        array('{timeAgo}', '{user}'),
                        array($agoTime, $user),
                        $data['text']
                    );

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
            $id = DM\FilesContent::toId($v, 'md5');

            if (!empty($id)) {
                $r = DM\FilesContent::read($id);
                //give affirmative result only if the correspondig file content exists
                $p[$k] = file_exists($filesDir . $r['path'] . DIRECTORY_SEPARATOR . $r['id'])
                    ? $r['id']
                    : null;
            } else {
                unset($p[$k]);
            }
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
                $d = DM\Files::getDuplicates($f['id']);
                $paths = array();
                if (sizeof($d) > 0) {
                    foreach ($d as $dup) {
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
                    $d = DM\Files::getDuplicates($f['id']);
                    if (sizeof($d) > 1) {
                        //msg: Following files have duplicates
                        $filenames[] = (empty($f['dir'])
                            ? ''
                            : $f['dir'] . DIRECTORY_SEPARATOR) . $f['name'];
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

    /**
     * checks if pid id exists in our tree or if filename exists under the pid.
     * @param  int    $pid
     * @param  string $name
     * @param  string $dir  an optional relative path under pid
     * @return [type]
     */
    public static function getFileId($pid, $name = '', $dir = '')
    {
        $rez = null;
        /* check if pid exists /**/

        $r = DM\Tree::read($pid);

        if (empty($r['dstatus'])) {
            $rez = $r['id'];
        }

        if (!empty($rez)) {
            if (!empty($name)) {
                $dir .= DIRECTORY_SEPARATOR . $name;
            }

            if (!empty($dir) && ($dir != '.')) {
                $dir = str_replace('\\', '/', $dir);
                $dir = explode('/', $dir);

                foreach ($dir as $dirName) {
                    if (empty($dirName) || ($dirName == '.')) {
                        continue;
                    }

                    $r = DM\Tree::getChildByName($rez, $dirName);

                    if (!empty($r)) {
                        $rez = $r['id'];
                    } else {
                        $rez = null;
                    }

                    if (empty($rez)) {
                        return $rez;
                    }
                }
            } else {
                $rez = null;
            }
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

        $f = DM\Files::read($id);

        if (!empty($f)) {
            $c = DM\FilesContent::read($f['content_id']);
            if (!empty($c['size'])) {
                $rez = intval($c['size']);
            }
        }

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
        foreach ($p['files'] as &$f) {
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
                            Note: we cant leave the previous file record if we have a given id for file
                        */

                        DM\Tree::delete($fileId, true);

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
        DM\Files::update(
            array(
                'id' => $p['id']
                ,'date' => Util\dateISOToMysql($p['date'])
                ,'title' => @$p['title']
                ,'uid' => User::getId()
                ,'udate' => 'CURRENT_TIMESTAMP'
            )
        );

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
        $userId = User::getId();

        foreach ($path as $dir) {
            if (empty($dir)) {
                continue;
            }

            $r = DM\Tree::getChildByName($pid, $dir);

            if (!empty($r)) {
                $pid = $r['id'];

            } else {
                $pid = DM\Tree::create(
                    array(
                        'pid' => $pid
                        ,'name' => $dir
                        ,'type' => 1 //?
                        ,'cid' => $userId
                        ,'uid' => $userId
                        ,'template_id' => Config::get('default_folder_template')
                    )
                );
            }
        }

        return $pid;
    }

    private function getFileMD5(&$file)
    {
        if (empty($file)) {
            return null;
        }

        return md5_file($file['tmp_name']) . 's' . $file['size'];
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

        $contentId = DM\FilesContent::toId($md5, 'md5');
        if (!empty($contentId)) {
            $content = DM\FilesContent::read($contentId);
            if (file_exists($filePath . $content['path'] . '/' . $content['id'])) {
                $f['content_id'] = $content['id'];
            }

        }

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

        $f['content_id'] = DM\FilesContent::create(
            array(
                'size' => $f['size']
                ,'type' => $f['type']
                ,'path' => $storage_subpath
                ,'md5' => $md5
            )
        );

        @mkdir($filePath . $storage_subpath . '/', 0777, true);

        if (copy($f['tmp_name'], $filePath . $storage_subpath . '/' . $f['content_id']) !== true) {
            throw new \Exception("Error copying file to destination folder, possible permission problems.", 1);
        }

        @unlink($f['tmp_name']);

        return true;
    }

    public function removeContentId($id)
    {
        $id = $id; //dummy codacy assignment
    }

    public static function minimizeUploadedFile(&$file)
    {
        switch ($file['type']) {
            case 'application/pdf':
                shell_exec('gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -dNOPAUSE -dQUIET -dBATCH -sOutputFile='.$file['tmp_name'].'_min '.$file['tmp_name']);
                if (file_exists($file['tmp_name'].'_min')) {
                    $file['tmp_name'] .='_min';
                    $file['size'] = filesize($file['tmp_name']);
                }
                break;
        }
    }

    public static function generatePreview($id, $versionId = false)
    {
        $rez = array();
        $file = array();
        $coreName = Config::get('core_name');
        $coreUrl = Config::get('core_url');

        $filesDir = Config::get('files_dir');
        $filesPreviewDir = Config::get('files_preview_dir');

        if (!empty($versionId)) {
            $file = DM\FilesVersions::read($versionId);
            $file['version_id'] = $versionId;
            $file['id'] = $id;
        } else {
            $file = DM\Files::read($id);
        }

        if (empty($file)) {
            \CB\debug('Error accessing file preview ('.$id.'). Record not found in DB.');

            return array('html' => '');
        }

        $content = DM\FilesContent::read($file['content_id']);
        $preview = DM\FilePreviews::read($content['id']);

        if (!empty($preview)) {
            switch ($preview['status']) {
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
        }

        $ext = explode('.', $file['name']);
        $ext = array_pop($ext);
        $ext = strtolower($ext);
        $rez['ext'] = $ext;

        $rez['filename'] = $content['id'].'_.html';

        $previewFilename = $filesPreviewDir . $rez['filename'];

        $fn = $filesDir . $content['path'] . DIRECTORY_SEPARATOR . $content['id'];
        // $nfn = $filesPreviewDir . $content['id'] . '_.' . $ext;

        if (!file_exists($fn)) {
            \CB\debug('Error accessing file preview ('.$id.'). Its content (id: '.@$content['id'].') doesnt exist on the disk.');

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
                if (empty($preview)) {
                    DM\FilePreviews::create(
                        array(
                            'id' => $content['id']
                            ,'group' => 'office'
                            ,'status' => 1
                            ,'filename' => null
                            ,'size' => 0
                            ,'cdate' => 'CURRENT_TIMESTAMP'
                        )
                    );
                }

                if (file_exists($previewFilename)) {
                    Files::deletePreview($content['id']);
                }

                $cmd = 'php -f '.LIB_DIR.'PreviewExtractorOffice.php -- -c '.$coreName . ' > '.Config::get('debug_log').'_office &';
                if (IS_WINDOWS) {
                    $cmd = 'start /D "'.LIB_DIR.'" php -f PreviewExtractorOffice.php -- -c '.$coreName . ' > '.Config::get('debug_log').'_office';
                }
                pclose(popen($cmd, "r"));

                return array('processing' => true);
                break;

            case 'xml':
            case 'htm':
            case 'html':
            case 'dhtml':
            case 'xhtml':

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
                file_put_contents($previewFilename, $content);
                //copy($fn, $previewFilename);
                break;

            case 'txt':
            case 'log':
            case 'css':
            case 'js':
            case 'json':
            case 'php':
            case 'bat':
            case 'ini':
            case 'sys':
            case 'sql':

                file_put_contents(
                    $previewFilename,
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
                $pfn = $filesPreviewDir . $content['id'];
                $convertedImages = array(
                    $pfn . '_.png'
                );

                if (!file_exists($convertedImages[0])) {
                    $convertedImages = array();
                    try {
                        $images = new \Imagick($fn);
                        $i = 1;
                        foreach ($images as $image) {
                            $image->setImageFormat('png');
                            $image->writeImage($pfn . $i . '_.png');
                            $convertedImages[] = $content['id'] . $i . '_.png';
                            $i++;
                        }
                    } catch (\Exception $e) {
                        return $rez;
                    }
                }

                file_put_contents(
                    $previewFilename,
                    '<img src="/' . $coreName . '/view/'.
                    implode(
                        '" class="fit-img" style="margin: auto" />' .
                        "<br /><hr />\n" .
                        '<img src="/' . $coreName . '/view/',
                        $convertedImages
                    ) .
                    '" class="fit-img" style="margin: auto" />'
                );
                break;

            default:
                if ((substr($content['type'], 0, 5) == 'image') && (substr($content['type'], 0, 9) !== 'image/svg')) {
                    file_put_contents(
                        $previewFilename,
                        '<div style="padding: 5px 10px"><img src="/' . $coreName . '/download/'.
                        $file['id'] .
                        (empty($version_id) ? '' : '/' . $version_id) .
                        '/" class="fit-img" style="margin: auto"></div>'
                    );
                }
        }

        if (!empty($preview)) {
            DM\FilePreviews::update(
                array(
                    'id' => $content['id']
                    ,'filename' => $rez['filename']
                )
            );

        } else {
            DM\FilePreviews::create(
                array(
                    'id' => $content['id']
                    ,'filename' => $rez['filename']
                )
            );
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
        $fileId = 0;

        //detect file id
        $version = DM\FilesVersions::read($id);

        if (!empty($version)) {
            $fileId = $version['file_id'];
            $rez['data']['id'] = $fileId;
        }

        //get its pid
        $r = DM\Tree::read($fileId);
        if (!empty($r['pid'])) {
            $rez['data']['pid'] = $r['pid'];
        }

        $this->saveCurrentVersion($fileId);

        DM\Files::delete($fileId);
        DM\Files::create(
            array(
                'id' => $fileId
                ,'content_id' => $version['content_id']
                ,'date' => $version['date']
                ,'name' => $version['name']
                ,'cid' => $version['cid']
                ,'uid' => User::getId()
                ,'cdate' => $version['cdate']
                ,'udate' => $version['udate']
            )
        );

        Objects::updateCaseUpdateInfo($id);

        Solr\Client::runCron();

        return $rez;
    }

    public function deleteVersion($id)
    {
        $rez = array('success' => true, 'id' => $id);
        $content_id = 0;

        $version = DM\FilesVersions::read($id);

        if (!empty($version)) {
            $content_id = $version['content_id'];
        }

        DM\FilesVersions::delete($id);

        $this->removeContentId($content_id);

        DM\Tree::update(
            array(
                'id' => $version['file_id']
                ,'updated' => 1
            )
        );

        Objects::updateCaseUpdateInfo($id);

        Solr\Client::runCron();

        return $rez;
    }
    /* end of versions */

    /**
     * merge files
     * To be reviewed
     *
     * @param  int  $ids
     * @return json response
     */
    public function merge($ids)
    {
        if (!is_array($ids)) {
            return array('success' => false);
        }

        $ids = Util\toNumericArray($ids);

        if (sizeof($ids) < 2) {
            return array('success' => false);
        }

        $to_id = null;
        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE id IN ('.implode(', ', $ids).')
            ORDER BY udate DESC, id DESC'
        );

        if ($r = $res->fetch_assoc()) {
            $to_id = $r['id'];
        }
        $res->close();

        DB\dbQuery(
            'UPDATE files_versions
            SET file_id = $1
            WHERE file_id IN ('.implode(', ', $ids).')',
            $to_id
        );

        $res = DB\dbQuery(
            'INSERT INTO files_versions (file_id, content_id, `date`, name, cid, uid, cdate, udate)
                SELECT $1
                    ,content_id
                    ,`date`
                    ,name
                    ,cid
                    ,uid
                    ,cdate
                    ,udate
                FROM files
                WHERE id <> $1
                    AND id in('.implode(',', $ids).')',
            $to_id
        );

        DB\dbQuery(
            'UPDATE tree
            SET did = $2
                    , dstatus = 1
                    , updated = (updated | 1)
            WHERE id <> $1
                AND id IN ('.implode(', ', $ids).')',
            array(
                $to_id
                ,User::getId()
            )
        );

        DM\Tree::update(
            array(
                'id' => $to_id
                ,'updated' => 1
            )
        );

        $ids = array_diff($ids, array($to_id));

        // Objects::updateCaseUpdateInfo($id);

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
