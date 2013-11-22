<?php
namespace Util;

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
        $this->DFT = \CB\getOption('DEFAULT_FOLDER_TEMPLATE');
        echo " DFT:".$this->DFT."\n";

        $this->verifyPid();

        $this->mainTemplateId = \Util\Templates::getMainTemplateId()
            or die('Cannot detect main template for templates');
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
                'Thesauri'
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
}
