<?php
namespace CB\Api;

use CB\Config;
use CB\DataModel as DM;

class Files
{
    /**
     * get file properties and content
     * @param  int  $id file id
     * @return json array with a subarray "data" that list all file properties and existent versions
     */
    public function get($id)
    {
        $rez = array('success' => true, 'data' => array());
        $file = new Objects\File($id);
        $rez['data'] = $file->load();

        $rez['data']['content'] = file_get_contents(
            \CB\Config::get('files_dir').
            $rez['data']['content_path'].DIRECTORY_SEPARATOR.
            $rez['data']['content_id']
        );
        unset($rez['data']['content_id']);
        unset($rez['data']['content_path']);

        return $rez;
    }

    /**
     * download a file
     *
     * outputs file content and set corresponding header params
     *
     * @param  int  $id file id
     * @return void
     */
    public function download($id, $asAttachment = true)
    {
        \CB\Files::download($id, null, $asAttachment);
    }

    /**
     * view file
     *
     * outputs file content and set corresponding header params
     *
     * @param  int  $id file id
     * @return void
     */
    public function view($id)
    {
        $this->download($id, false);
    }

    /**
     * upload a file to CaseBox using post method
     *
     *
     * @param array $p {
     *    int           $pid        parent object Id
     *    varchar       $localFile  the absolute location of a file on the same server  /var/www/website.com/book.pdf
     *    int           $template_id | $tmplId  Template id of the file to be added.
     *                      In CaseBox each tree object has a template.
     *                      If this param is not specified then default_file_template is used (if defined in core config).
     *    array         $data | $tmplData    file metadata according to the template {'language': 'english', 'price': '$10'}
     *    isodate       $date    the date in mysql format    2012-03-27T10:25
     *    varchar|int   $oid | $owner   the username or id of the file owner
     *    varchar       $title | $filename    the title that will replace the original filename of the uploaded file  new-book.pdf
     *    varchar       $fileExistAction = (newversion|replace|autorename)
     *                      Action to be taken when file exist in target.
     * }
     *    'file' name of the POST variable from Files when posting a file (multipart/form-data).
     * @return json responce
     */
    public function upload($p)
    {
        /*check params validity */
        $params_validation = $this->validateInputParamsForUpload($p);
        if ($params_validation !== true) {
            throw new \Exception("Params validation failed: ".$params_validation, 1);
        }
        /* end of check params validity */

        if (empty($p['data']) && !empty($p['tmplData'])) {
            $p['data'] = $p['tmplData'];
            unset($p['tmplData']);
        }

        $files = new \CB\Files();
        if (empty($p['response']) && $files->fileExists($p['pid'], $p['title'])) {
            throw new \Exception("File exists in target: ".$p['title'], 1);
        }

        if (!empty($p['localFile'])) {
            $file_name = basename($p['localFile']);
            $tmp_name = Config::get('incomming_files_dir') . $file_name;
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            copy($p['localFile'], $tmp_name);

            $p['files']['file'] = array(
                'name' => empty($p['title']) ? $file_name : $p['title']
                ,'tmp_name' => $tmp_name
                ,'error' => 0
                ,'size' => filesize($p['localFile'])
                ,'type' => finfo_file($finfo, $tmp_name)
            );
        } else {
            $p['files'] = &$_FILES;
        }
        if (empty($p['files']) || ($p['files']['file']['error'] !== UPLOAD_ERR_OK)) {
            throw new \Exception('File upload error', 1);
        }
        $files->storeFiles($p);

        $rez = array('success' => true, 'data' => $p['files']);

        return $rez;
    }

    private function validateInputParamsForUpload(&$p)
    {
        if (!isset($p['pid'])) {
            return 'pid not specified';
        }

        if (!is_numeric($p['pid'])) {
            return 'pid not valid';
        }

        if (empty($p['template_id']) && !empty($p['tmplId'])) {
            $p['template_id'] = $p['tmplId'];
        }

        if (empty($p['template_id'])) {
            $p['template_id'] = \CB\Config::get('default_file_template');

            if (empty($p['template_id'])) {
                return 'template not specified';
            }
        }

        if (!empty($p['fileExistAction'])) {
            if (!in_array($p['fileExistAction'], array('newversion', 'replace', 'autorename'))) {
                return 'Invalid value for fileExistAction';
            }
            $p['response'] = $p['fileExistAction'];
            unset($p['fileExistAction']);
        }

        if (!is_numeric($p['template_id'])) {
            return 'template id not valid';
        }

        if (!empty($p['localFile'])) {
            if (!file_exists($p['localFile'])) {
                return 'File not found: '.$p['localFile'];
            }
        } else {
            if (empty($_FILES)) {
                return 'No file found for upload';
            }
        }

        if (empty($p['title'])) {
            if (!empty($p['filename'])) {
                $p['title'] = $p['filename'];
                unset($p['filename']);
            } else {
                if (!empty($p['localFile'])) {
                    $p['title'] = basename($p['localFile']);
                } elseif (!empty($_FILES['file'])) {
                    $p['title'] = $_FILES['file']['name'];
                }
            }
        }
        if (empty($p['title'])) {
            return 'Cannot detect file title';
        }

        if (!isset($p['oid'])) {
            if (!isset($p['owner'])) {
                return 'owner not specified';
            }

            if (is_numeric($p['owner'])) {
                if (DM\Users::idExists($p['owner'])) {
                    $p['oid'] = $p['owner'];
                }
            } else {
                $p['oid'] = DM\Users::getIdByName($p['owner']);
            }
        }

        if (!is_numeric($p['oid'])) {
            return 'invalid owner specified';
        } elseif (empty($p['cid'])) {
            $p['cid'] = $p['oid'];
        }

        return true;
    }
}
