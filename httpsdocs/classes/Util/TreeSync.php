<?php
namespace Util;

use CB\DB;
use CB\L;

class TreeSync
{
    protected $mainPid = null;
    protected $DFT = null;
    protected $targetFolderName = 'TreeSync';

    public function __construct($pid = null)
    {
        $this->mainPid = $pid;

    }

    protected function init()
    {
        echo "init\n";
        $this->DFT =
            empty($GLOBALS['DFT'])
            ? \CB\getOption('DEFAULT_FOLDER_TEMPLATE')
            : $GLOBALS['DFT'];

        echo " DFT:".$this->DFT."\n";

        $this->verifyPid();

        $this->mainTemplateId = \Util\Templates::getTemplateId(
            array(
                'name' => 'Templates template'
                ,'type' => 'template'
            )
        ) or die('Cannot detect main template for templates');

        echo " mainTemplateId:".$this->mainTemplateId."\n";

    }

    /**
     * check $pid existance and create Thesauri Folder if needed
     * @return void
     */
    protected function verifyPid()
    {
        $rez = true;
        if (is_numeric($this->mainPid)) {
            if (!\CB\Objects::idExists($this->mainPid)) {
                die('Specified target id does not exist');
            }
        } else {
            $rootFolderId = \CB\Browser::getRootFolderId();
            $this->mainPid = \CB\Objects::getChildId(
                $rootFolderId,
                $this->targetFolderName
            );
            if (is_null($this->mainPid)) {
                $folderObj = new \CB\Objects\Object();
                $this->mainPid = $folderObj->create(
                    array(
                        'pid' => $rootFolderId
                        ,'name' => $this->targetFolderName
                        ,'template_id' => $this->DFT
                    )
                ) or die('Error creating '.$this->targetFolderName.' folder');
            }
        }
    }

    protected function prepareExecution()
    {
        $this->init();

        echo "Start transaction\n";
        DB\startTransaction();

        $this->genericObject = new \CB\Objects\Object();

        // prepare languages association for fields
        $languages = \CB\getOption('LANGUAGES');
        $fields = L\languageStringToFieldNames($languages);
        $languages = explode(',', $languages);
        $fields = explode(',', $fields);
        $this->languageFields = array_combine($fields, $languages);

    }
}
