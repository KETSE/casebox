<?php

namespace CB\Install;

use CB\Objects;
use CB\Util;
use CB\DB;
use CB\DataModel as DM;

/**
 * Vanilla model script
 * designed to be applied on cores
 * instantiated from bare bone core
 */

class VanillaModel
{

    /**
     * grlobal script cofig that contain all options
     * @var array
     */
    protected $config = array(
        /******************************** GLOBAL *******************************/
        'folderTemplateId' => 5
        ,'thesauriTemplateId' => 8
        ,'templatesTemplateId' => 11
        ,'fieldTemplateId' => 12

        ,'templatesFolderId' => 3
        ,'thesauriFolderId' => 4

        /*************************** USERS AND GROUPS **************************/
        ,'groups' => [
            'Administrators'
            ,'Lawyers'
        ]

        ,'users' => []

        /******************************* THESAURI ******************************/
        ,'thesauri' => array(
            // 'task' => array(
            //     //'tags' => array()
            //     //,'importance ( Critical – High – Medium- Low)
            //     //,'status (new – in progress – completed )

            // ),

            // 'link' => array(
            //     'type' => array(
            //         'Video'
            //         ,'Document'
            //         ,'Article'
            //         ,'Image'
            //         ,'Sound'
            //         ,'Website'
            //     )
            //     //,'tag' => array()
            // )

            // Casebox Vanilla
            'case' => array(
                'type' => array(
                    'Civil'
                    ,'Constitutional'
                    ,'Human Rights'
                    ,'Criminal'
                    ,'Military'
                )
                ,'tags' => array(
                    'Torture'
                    ,'Assault'
                    ,'Murder'
                    ,'Housing'
                    ,'Child'
                    ,'Arrest'
                    ,'Health'
                )
                ,'status' => array(
                    'New'
                    ,'Ongoing'
                    ,'On Hold'
                    ,'Archived'
                    ,'Closed'
                )
                ,'contact role' => array(
                    'Defendant'
                    ,'Government Official'
                    ,'Lawyer'
                    ,'Perpetrator'
                    ,'Relative'
                    ,'Source'
                    ,'Victim'
                    ,'Witness'
                )
            )
            ,'Country' => array()
            ,'Region' => array()
        )

        /******************************* TEMPLATES ******************************/

        ,'templates' => array(
            'Contact' => array(
                'type' => 'object'

                ,'fields' => array(
                    'first_name' => array('en' => 'First Name')
                    ,'last_name' => array('en' => 'Last Name')
                    ,'title' => array('en' => 'Title')
                    ,'organization' => array('en' => 'Organization')
                    ,'email' => array('en' => 'Email')
                    ,'phone' => array('en' => 'Phone')
                    ,'country' => array('en' => 'Country')
                )
            )

            ,'Organization' => array(
                'type' => 'object'

                ,'fields' => array(
                    'name' => array('en' => 'Name')
                    ,'email' => array('en' => 'Email')
                    ,'phone' => array('en' => 'Phone')
                    ,'country' => array(
                        'en' => 'Country'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'source' => 'tree'
                            ,'scope' => '/Country'
                        )
                    )
                    ,'website' => array('en' => 'Website')
                    ,'facebook' => array('en' => 'Facebook')
                    ,'twitter' => array('en' => 'Twitter')
                )
            )

            ,'Case' => array(
                'type' => 'case'
                ,'iconCls' => 'icon-briefcase'

                ,'fields' => array(
                    'name' => array('en' => 'Name')
                    ,'status' => array(
                        'en' => 'Case status'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'source' => 'tree'
                            ,'scope' => '/case/status'
                        )
                    )
                    ,'tags' => array(
                        'en' => 'Tags'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'source' => 'tree'
                            ,'multiValued' => true
                            ,'scope' => '/case/tags'
                        )
                    )
                    ,'type' => array(
                        'en' => 'Type'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'source' => 'tree'
                            ,'scope' => '/case/type'
                        )
                    )
                    ,'date' => array(
                        'en' => 'Date'
                        ,'type' => 'date'
                    )
                    ,'description' => array(
                        'en' => 'Description'
                        ,'type' => 'text'
                    )
                    ,'region' => array(
                        'en' => 'Region'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'source' => 'tree'
                            ,'scope' => '/Region'
                        )
                    )
                    ,'country' => array(
                        'en' => 'Country'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'source' => 'tree'
                            ,'scope' => '/Country'
                        )
                    )
                    ,'contacts' => array(
                        'en' => 'Case contacts'
                        ,'type' => '_objects'
                        ,'cfg' => array(
                            'source' => 'tree'
                            ,'multiValued' => true
                            // ,'scope' => null
                        )
                    )
                )
            )
        )
    );

    /**
     * applying vanilla changes to current core
     * @return void
     */
    public function apply()
    {
        //2.  Casebox Users and Groups
        $this->setupUsersGroups();

        // 6.  Thesauri
        echo "\nCreate thesauri items .. ";
        $this->createThesauri(
            $this->config['thesauriFolderId'],
            $this->config['thesauri'],
            '/'
        );

        echo "Done\n";

        // 3.  File Folder Structure
        $this->structureFilesAndFolders();

        // 4.  Custom Entities
        echo "\nCreate custom templates .. ";
        $this->addCustomTemplates();
        echo "Done\n";

        $this->updateCreateMenus();
    }

    /**
     * add default users and groups
     * @return void
     */
    protected function setupUsersGroups()
    {
        $groups = array(
            'Administrators'
            ,'Lawyers'
        );

        foreach ($groups as $group) {
            echo "creating group '$group' .. ";

            $id = DM\Group::getIdByName($group);

            if (empty($id)) {
                DM\Group::create(array('name' => $group));
            }

            echo "ok\n";
        }
    }

    /**
     * [structureFilesAndFolders description]
     * @return [type] [description]
     */
    protected function structureFilesAndFolders()
    {
    }

    /**
     * create thesauri
     * @return void
     */
    protected function createThesauri($pid, &$th, $prefix)
    {
        $o = new \CB\Objects\Object();

        foreach ($th as $k => &$v) {
            //create $k folder under pid
            echo "creating '$k' .. ";

            $id = $o->create(
                array(
                    'id' => null
                    ,'pid' => $pid
                    ,'template_id' => $this->config['folderTemplateId']
                    ,'data' => array(
                        '_title'  => $k
                    )
                )
            );

            $this->thesauriIds[$prefix . $k] = $id;
            echo "ok\n";

            if (Util\isAssocArray($v)) {
                //subfolders
                $this->createThesauri($id, $v, $prefix . $k . '/');

            } else {
                //create thesauri items
                $i = 1;
                foreach ($v as $item) {
                    $o->create(
                        array(
                            'id' => null
                            ,'pid' => $id
                            ,'template_id' => $this->config['thesauriTemplateId']
                            ,'name' => $item
                            ,'data' => array(
                                "en" => $item
                                ,"iconCls" => "icon-tag-small"
                                ,"visible" => 1
                                ,"order" => $i++
                            )
                        )
                    );
                }
            }
        }
    }

    /**
     * create templates
     * @return void
     */
    protected function addCustomTemplates()
    {
        $o = new \CB\Objects\Template();
        $tf = new \CB\Objects\TemplateField();

        foreach ($this->config['templates'] as $k => $v) {
            echo "creating template '$k' .. ";

            $v['id'] = null;
            $v['pid'] = $this->config['templatesFolderId'];
            $v['template_id'] = $this->config['templatesTemplateId'];

            //create correct data
            $name = empty($v['name'])
                ? $k
                : $v['name'];

            $type = empty($v['type'])
                ? 'object'
                : $v['type'];

            $data = array(
                '_title' => $k
                ,'en' => $name
                ,'type' => $type
                ,'visible' => 1
            );

            if (!empty($v['iconCls'])) {
                $data['iconCls'] = $v['iconCls'];
            }
            if (!empty($v['cfg'])) {
                $data['cfg'] = $v['cfg'];
            }
            if (!empty($v['title_template'])) {
                $data['title_template'] = $v['title_template'];
            }

            $v['data'] = $data;

            $fields = empty($v['fields'])
                ? array()
                : $v['fields'];

            unset($v['fields']);

            echo "Ok\n";

            $id = $o->create($v);

            $this->templateIds[$k] = $id;

            // analize fields
            // ,'country' => array(
            //             'en' => 'Country'
            //             ,'type' => '_objects'
            //             ,'cfg' => array(
            //                 'source' => 'tree'
            //                 ,'scope' => '/Country'
            //             )
            //         )

            $i = 1;
            foreach ($fields as $fn => $fv) {
                $fv['id'] = null;
                $fv['pid'] = $id;
                $fv['template_id'] = $this->config['fieldTemplateId'];

                $name = empty($fv['en'])
                    ? $fn
                    : $fv['en'];

                $type = empty($fv['type'])
                    ? 'varchar'
                    : $fv['type'];

                $order = empty($fv['order'])
                    ? $i++
                    : $fv['order'];

                $data = array(
                    'name' => $fn
                    ,'en' => $name
                    ,'type' => $type
                    ,'order' => $order
                );

                if (!empty($fv['solr_column_name'])) {
                    $data['solr_column_name'] = $fv['solr_column_name'];
                }

                $cfg = empty($fv['cfg'])
                    ? array()
                    : $fv['cfg'];

                if (!empty($cfg['scope']) && substr($cfg['scope'], 0, 1) == '/') {
                    $cfg['scope'] = $this->thesauriIds[$cfg['scope']];
                }

                if (!empty($cfg)) {
                    $data['cfg'] = Util\jsonEncode($cfg);
                }

                $fv['name'] = $name;
                $fv['type'] = $type;
                $fv['order'] = $order;
                $fv['data'] = $data;

                $tf->create($fv);
            }

            /*
            {"_title":"assigned"
            ,"en":"Assigned"
            ,"type":"_objects"
            ,"order":7
            ,"cfg":"{
                 \"editor\": \"form\"
                ,\"source\": \"users\"
                ,\"renderer\": \"listObjIcons\"
                 ,\"autoLoad\": true
                 ,\"multiValued\": true
                 ,\"hidePreview\": true\n}"
            }
             */

            echo "Ok\n";
        }
    }

    /**
     * update create menus
     * @return void
     */
    protected function updateCreateMenus()
    {
        //add case template at the begining of default menu
        DB\dbQuery(
            'UPDATE menu
            SET menu = CONCAT($1, menu)
            WHERE node_ids IS NULL
                AND node_template_ids IS NULL',
            $this->templateIds['Case'] . ',\'-\','
        ) or die(DB\dbQueryError());

    }
}
