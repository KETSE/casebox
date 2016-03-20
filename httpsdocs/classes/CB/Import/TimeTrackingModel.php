<?php
namespace CB\Import;

use CB\Config;
use CB\DB;
use CB\DataModel as DM;
use CB\Templates;
use CB\Browser;
use CB\Objects;
use CB\Import\BareBoneModel as BBM;

class TimeTrackingModel extends Base
{

    /**
     * grlobal script cofig that contain all options
     * @var array
     */
    protected $cfg = array(

        /******************************* TEMPLATES ******************************/

        'templates' => array(
            'Time spent' => array(
                'type' => 'time_tracking'

                ,'fields' => array(
                    '_date_start' => array(
                        'en' => 'Date'
                        ,'ru' => 'Дата'
                        ,'type' => 'datetime'
                        ,'cfg' => array(
                            'value' => 'now'
                        )
                    )
                    // ,'time' => array(
                    //     'en' => 'At'
                    //     ,'ru' => 'Время'
                    //     ,'type' => 'time'
                    //     // ,'cfg' => array(
                    //     //     'indexed' => true
                    //     // )
                    //     // ,'solr_column_name' => 'time_spent_i'
                    // )
                    ,'time_spent' => array(
                        'en' => 'Time spent'
                        ,'ru' => 'Потраченное время'
                        ,'type' => 'time'
                        ,'cfg' => array(
                            'indexed' => true
                        )
                        ,'solr_column_name' => 'time_spent_i'
                    )
                    ,'comment' => array(
                        'en' => 'Comment'
                        ,'ru' => 'Коментарий'
                        ,'type' => 'varchar'
                    )
                    ,'cost' => array(
                        'en' => 'Hourly rare'
                        ,'ru' => 'Почасовая ставка'
                        ,'type' => 'varchar'
                        ,'cfg' => array(
                            'readOnly' => true
                        )
                    )
                )
            )
        )
    );

    protected function init()
    {
        parent::init();

        $ids = DM\Templates::getIdsByType('time_tracking');

        if (!empty($ids)) {
            throw new \Exception("This core seem to have time tracking template already", 1);
        }
    }

    /**
     * executing preimporting changes to target core
     * @return void
     */
    protected function prepare()
    {
        //update template types
        DB\dbQuery(
            "ALTER TABLE `templates`
            CHANGE `type` `type` ENUM('case','object','file','task','user','email','template','field','search','comment','shortcut','menu','config','time_tracking')
            CHARSET utf8 COLLATE utf8_general_ci NULL"
        );

        // set templates template id in config
        $ids = DM\Templates::getIdsByType('template');
        $id = array_shift($ids);

        BBM::$cfg['templatesTemplateId'] = $id;

        // detect fields template id
        $ids = DM\Templates::getIdsByType('field');
        $id = array_shift($ids);

        BBM::$cfg['fieldTemplateId'] = $id;

        //detect folderTemplateId
        $ids = Config::get('folder_templates');
        if (!empty($ids)) {
            BBM::$cfg['folderTemplateId'] = array_shift($ids);
        }

        $o = new \CB\Objects\Object();

        $rootId = Browser::getRootFolderId();

        $pid = Objects::getChildId($rootId, 'Templates');
        if (empty($pid)) {
            $pid = Objects::getChildId($rootId, 'System');
            if (!empty($pid)) {
                $pid = Objects::getChildId($pid, 'Templates');
            }
        }

        BBM::$cfg['templatesFolderId'] = $pid;
    }

    /**
     * applying vanilla changes to current core
     * @return void
     */
    public function execute()
    {
        echo "\nCreate custom templates .. ";
        $this->addCustomTemplates();
        echo "Done\n";
    }
}
