<?php

namespace CB\Import;

use CB\Config;
use CB\DB;
use CB\DataModel as DM;
use CB\Templates;
use CB\Browser;
use CB\Objects;
use CB\Import\BareBoneModel as BBM;

class UpgradeMenuModel extends Base
{

    /**
     * grlobal script cofig that contain all options
     * @var array
     */
    protected $cfg = array(

        /******************************* TEMPLATES ******************************/

        'templates' => array(
            '- Menu separator -' => array(
                'type' => 'object'
            )

            ,'Menu rule' => array(
                'type' => 'menu'

                ,'fields' => array(
                    '_title' => array('en' => 'Title')
                    ,'node_ids' => array(
                        'en' => 'Nodes'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'multiValued' => true
                            ,'editor' => 'form'
                            ,'renderer' => 'listObjIcons'
                        )
                    )
                    ,'template_ids' => array(
                        'en' => 'Templates'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'templates' => null // sould be set after core init
                            ,'editor' => 'form'
                            ,'multiValued' => true
                            ,'renderer' => 'listObjIcons'
                        )
                    )
                    ,'user_group_ids' => array(
                        'en' => 'Users/Groups'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'source' => 'usersgroups'
                            ,'multiValued' => true
                        )
                    )
                    ,'menu' => array(
                        'en' => 'Menu'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'templates' => null // sould be set after core init
                            ,'multiValued' => true
                            ,'editor' => 'form'
                            ,'allowValueSort' => true
                            ,'renderer' => 'listObjIcons'
                        )
                    )
                )
            )
        )
    );

    protected function init()
    {
        parent::init();

        //check if menu table exists in db
        //otherwise the core is already upgraded
        $res = DB\dbQuery('SELECT * FROM `menu`');

        if (empty($res)) {
            throw new \Exception("This core seem to have upgraded menu already", 1);
        }

        $res->close();
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
            CHANGE `type` `type` enum('case','object','file','task','user','email','template','field','search','comment','shortcut','menu')
            COLLATE utf8_general_ci NULL after `is_folder`"
        );

        // set templates template id in config
        $ids = DM\Templates::getIdsByType('template');
        $id = array_shift($ids);

        BBM::$cfg['templatesTemplateId'] = $id;
        $this->cfg['templates']['Menu rule']['fields']['template_ids']['cfg']['templates'] = $id;
        $this->cfg['templates']['Menu rule']['fields']['menu']['cfg']['templates'] = $id;

        // detect fields template id
        $ids = DM\Templates::getIdsByType('field');
        $id = array_shift($ids);

        BBM::$cfg['fieldTemplateId'] = $id;

        //detect folderTemplateId
        $ids = Config::get('folder_templates');
        if (!empty($ids)) {
            BBM::$cfg['folderTemplateId'] = array_shift($ids);
        }

        //create "Menu" folder under templates to store our menu templates there
        //and update BBM::$cfg['templatesFolderId'] to our folder id
        $o = new \CB\Objects\Object();

        $rootId = Browser::getRootFolderId();

        $pid = Objects::getChildId($rootId, 'Templates');
        if (empty($pid)) {
            $pid = Objects::getChildId($rootId, 'System');
            if (!empty($pid)) {
                $pid = Objects::getChildId($pid, 'Templates');
            }
        }

        $id = $o->create(
            array(
                'id' => null
                ,'pid' => $pid
                ,'template_id' => BBM::$cfg['folderTemplateId']
                ,'name' => 'Menu'
                ,'data' => array(
                    '_title'  => 'Menu'
                )
            )
        );

        BBM::$cfg['templatesFolderId'] = $id;

        //create System/Menus folder for transferring menu rules to it
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

        $this->cfg['menusFolderId'] = $o->create(
            array(
                'id' => null
                ,'pid' => $pid
                ,'template_id' => BBM::$cfg['folderTemplateId']
                ,'name' => 'Menus'
                ,'data' => array(
                    '_title'  => 'Menus'
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

        $this->convertMenuRulesToTree();

        DB\dbQuery('DROP TABLE menu');
    }

    /**
     * transfer menu rules to tree
     * @return void
     */
    protected function convertMenuRulesToTree()
    {
        $o = new \CB\Objects\Object();

        $res = DB\dbQuery('SELECT * FROM menu');

        while ($r = $res->fetch_assoc()) {
            // $menu = Util\toTrimmedArray($r['menu'], ',');

            //replace splitters
            $r['menu'] = str_replace('-', $this->templateIds['- Menu separator -'], $r['menu']);

            $o->create(
                array(
                    'id' => null
                    ,'pid' => $this->cfg['menusFolderId']
                    ,'template_id' => $this->templateIds['Menu rule']
                    ,'name' => '#' . $r['id']
                    ,'data' => array(
                        '_title'  => '#' . $r['id'],
                        'node_ids' => $r['node_ids'],
                        'template_ids' => $r['node_template_ids'],
                        'user_group_ids' => $r['user_group_ids'],
                        'menu' => $r['menu']
                    )
                )
            );
        }

        $res->close();

        //add menu rule for Menus folder
        $o->create(
            array(
                'id' => null
                ,'pid' => $this->cfg['menusFolderId']
                ,'template_id' => $this->templateIds['Menu rule']
                ,'name' => 'Create menu rules in this folder'
                ,'data' => array(
                    '_title'  => 'Create menu rules in this folder',
                    'node_ids' => $this->cfg['menusFolderId'],
                    'menu' => $this->templateIds['Menu rule']
                )
            )
        );

    }
}
