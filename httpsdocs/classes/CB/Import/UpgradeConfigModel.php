<?php

namespace CB\Import;

use CB\Config;
use CB\DB;
use CB\DataModel as DM;
use CB\Templates;
use CB\Browser;
use CB\Objects;
use CB\Util;
use CB\Import\BareBoneModel as BBM;

class UpgradeConfigModel extends Base
{

    /**
     * grlobal script cofig that contain all options
     * @var array
     */
    protected $cfg = array(

        /******************************* TEMPLATES ******************************/

        'templates' => array(
            'Config int option' => array(
                'type' => 'config'
                ,'iconCls' => 'icon-element'

                ,'fields' => array(
                    '_title' => array('en' => 'Name')
                    ,'value' => array(
                        'en' => 'Value'
                        ,'type' => 'int'
                    )
                )
            )
            ,'Config varchar option' => array(
                'type' => 'config'
                ,'iconCls' => 'icon-element'

                ,'fields' => array(
                    '_title' => array('en' => 'Name')
                    ,'value' => array(
                        'en' => 'Value'
                        ,'type' => 'varchar'
                    )
                )
            )
            ,'Config text option' => array(
                'type' => 'config'
                ,'iconCls' => 'icon-element'

                ,'fields' => array(
                    '_title' => array('en' => 'Name')
                    ,'value' => array(
                        'en' => 'Value'
                        ,'type' => 'text'
                    )
                )
            )
            ,'Config json option' => array(
                'type' => 'config'
                ,'iconCls' => 'icon-element'

                ,'fields' => array(
                    '_title' => array('en' => 'Name')
                    ,'value' => array(
                        'en' => 'Value'
                        ,'type' => 'text'
                        ,'cfg' => array(
                            "editor" => "ace"
                            ,"format" => "json"
                            ,"validator" => "json"
                        )
                    )
                    ,'order' => array(
                        'en' => 'Order'
                        ,'type' => 'int'
                        ,'cfg' => array(
                            "indexed" => true
                        )
                        ,'solr_column_name' => 'order'
                    )
                )
            )
        )
    );

    /**
     * executing preimporting changes to target core
     * @return void
     */
    protected function prepare()
    {
        //update template types
        DB\dbQuery(
            "ALTER TABLE `templates`
              CHANGE `type` `type` ENUM('case','object','file','task','user','email','template','field','search','comment','shortcut','menu','config')
              CHARSET utf8 COLLATE utf8_general_ci NULL"
        ) or die(DB\dbQueryError());

        // set templates template id in config
        $ids = Templates::getIdsByType('template');
        $id = array_shift($ids);

        BBM::$cfg['templatesTemplateId'] = $id;
        // $this->cfg['templates']['Menu rule']['fields']['template_ids']['cfg']['templates'] = $id;
        // $this->cfg['templates']['Menu rule']['fields']['menu']['cfg']['templates'] = $id;

        // detect fields template id
        $ids = Templates::getIdsByType('field');
        $id = array_shift($ids);

        BBM::$cfg['fieldTemplateId'] = $id;

        //detect folderTemplateId
        $ids = Config::get('folder_templates');
        if (!empty($ids)) {
            BBM::$cfg['folderTemplateId'] = array_shift($ids);
        }

        //create "Config" folder under templates to store our config templates there
        //and update BBM::$cfg['templatesFolderId'] to our folder id
        $o = new \CB\Objects\Object();

        $rootId = Browser::getRootFolderId();
        $this->systemFolderId = Objects::getChildId($rootId, 'System');

        $pid = Objects::getChildId($rootId, 'Templates');
        if (empty($pid)) {
            if (!empty($this->systemFolderId)) {
                $pid = Objects::getChildId($this->systemFolderId, 'Templates');
            }
        }

        $id = $o->create(
            array(
                'id' => null
                ,'pid' => $pid
                ,'template_id' => BBM::$cfg['folderTemplateId']
                ,'name' => 'Config'
                ,'data' => array(
                    '_title'  => 'Config'
                )
            )
        );

        BBM::$cfg['templatesFolderId'] = $id;

        //create System/Config folder for transferring config options
        $pid = Objects::getChildId($rootId, 'System');
        if (empty($pid)) {
            $pid = $o->create(
                array(
                    'id' => null
                    ,'pid' => $rootId
                    ,'template_id' => BBM::$cfg['folderTemplateId']
                    ,'name' => 'System'
                    ,'data' => array(
                        '_title'  => 'System'
                    )
                )
            );
        }

        $this->cfg['configFolderId'] = $o->create(
            array(
                'id' => null
                ,'pid' => $pid
                ,'template_id' => BBM::$cfg['folderTemplateId']
                ,'name' => 'Config'
                ,'data' => array(
                    '_title'  => 'Config'
                )
            )
        );

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

        $this->syncConfigToTree();
    }

    /**
     * transfer config options to tree
     * @return void
     */
    protected function syncConfigToTree()
    {
        $o = new \CB\Objects\Object();
        $co = new \CB\Objects\Config();

        $res = DB\dbQuery('SELECT * FROM config') or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            //detect option type
            $type = '';

            switch ($r['param']) {
                case 'default_event_template':
                case 'default_file_template':
                case 'default_folder_template':
                case 'default_task_template':
                    $type = 'int';
                    break;

                case 'default_language':
                case 'languages':
                case 'project_name_en':
                case 'project_name_ru':
                    $type = 'varchar';
                    break;

                case 'folder_templates':
                case 'max_files_version_count':
                case 'templateIcons':
                    $type = 'text';
                    break;

                case 'facet_configs':
                case 'js':
                case 'maintenance_cfg':
                case 'node_facets':
                case 'rootNode':
                case 'object_type_plugins':
                case 'treeNodes':
                    $type = 'json';
                    break;

                case 'responsible_party':
                case 'responsible_party_default':
                case 'task_categories':
                case 'maintenance_mode':
                    continue;

                default:
                    if (is_numeric($r['value'])) {
                        $type = 'int';
                    } else {
                        $type = 'text';
                    }
            }

            if (empty($type)) {
                continue;
            }

            $childs = array();

            if ($r['param'] == 'folder_templates') {
                $r['value'] .= ',' . $this->templateIds["Config json option"];
                DM\Config::update($r);
            }

            if ($r['param'] == 'treeNodes') {
                $childs = Util\toJSONArray($r['value']);
                $r['value'] = '';

                DM\Config::update($r);
            }

            $pid = $o->create(
                array(
                    'id' => null
                    ,'pid' => $this->cfg['configFolderId']
                    ,'template_id' => $this->templateIds["Config $type option"]
                    ,'name' => $r['param']
                    ,'data' => array(
                        '_title'  => $r['param'],
                        'value' => $r['value']
                    )
                )
            );

            $i = 1;
            foreach ($childs as $k => $v) {
                $co->create(
                    array(
                        'id' => null
                        ,'pid' => $pid
                        ,'template_id' => $this->templateIds["Config $type option"]
                        ,'name' => $k
                        ,'data' => array(
                            '_title'  => $k,
                            'value' => json_encode($v, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                            'order' => $i++
                        )
                    )
                );
            }
        }

        $res->close();

        //add menu rule for Menus folder

        $pid = Objects::getChildId($this->systemFolderId, 'Menus');
        $tempalteIds = Templates::getIdsByType('menu');

        $o->create(
            array(
                'id' => null
                ,'pid' => $pid
                ,'template_id' => $tempalteIds[0]
                ,'name' => 'Create config options rule'
                ,'data' => array(
                    '_title'  => 'Create config options rule',
                    'node_ids' => $this->cfg['configFolderId'],
                    'menu' => $this->templateIds['Config int option'] . ',' .
                        $this->templateIds['Config varchar option'] . ',' .
                        $this->templateIds['Config text option'] . ',' .
                        $this->templateIds['Config json option']
                )
            )
        );

    }
}
