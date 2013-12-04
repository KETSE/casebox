<?php
namespace Util\Templates;

use CB\DB;
use CB\Browser;
use CB\Objects;

class TreeSync extends \Util\TreeSync
{
    protected $targetFolderName = 'Templates';

    private $tTConfig = array(
        'name' => 'Templates template'
        ,'custom_title' => null
        ,'l1' => 'Templates template'
        ,'l2' => 'Templates template'
        ,'l3' => 'Templates template'
        ,'l4' => 'Templates template'
        ,'template_id' => null //will be set later when processing
        ,'iconCls' => 'icon-template'
        ,'type' => 'template'
        ,'fields' => array(
            array(
                'name' => '_title'
                ,'l1' => 'Name'
                ,'l2' => 'Name'
                ,'l3' => 'Name'
                ,'l4' => 'Name'
                ,'type' => 'varchar'
                ,'order' => 0
                ,'cfg' => array(
                    'showIn' => 'top'
                    ,'readOnly' => true
                )
            )
            ,array(
                'name' => 'type'
                ,'l1' => 'Type'
                ,'l2' => 'Type'
                ,'l3' => 'Type'
                ,'l4' => 'Type'
                ,'type' => '_templateTypesCombo'
                ,'order' => 5
            )
            ,array(
                'name' => 'visible'
                ,'l1' => 'Active'
                ,'l2' => 'Active'
                ,'l3' => 'Active'
                ,'l4' => 'Active'
                ,'type' => 'checkbox'
                ,'order' => 6
                ,'cfg' => array(
                    'showIn' => 'top'
                )
            )
            ,array(
                'name' => 'iconCls'
                ,'l1' => 'Icon class'
                ,'l2' => 'Icon class'
                ,'l3' => 'Icon class'
                ,'l4' => 'Icon class'
                ,'type' => 'iconcombo'
                ,'order' => 7
            )
            ,array(
                'name' => 'cfg'
                ,'l1' => 'Config'
                ,'l2' => 'Config'
                ,'l3' => 'Config'
                ,'l4' => 'Config'
                ,'type' => 'text'
                ,'order' => 8
                ,'cfg' => array(
                    'height' => 100
                )
            )
            ,array(
                'name' => 'title_template'
                ,'l1' => 'Title template'
                ,'l2' => 'Title template'
                ,'l3' => 'Title template'
                ,'l4' => 'Title template'
                ,'type' => 'text'
                ,'order' => 9
                ,'cfg' => array(
                    'height' => 50
                )
            )
            ,array(
                'name' => 'info_template'
                ,'l1' => 'Info template'
                ,'l2' => 'Info template'
                ,'l3' => 'Info template'
                ,'l4' => 'Info template'
                ,'type' => 'text'
                ,'order' => 10
                ,'cfg' => array(
                    'height' => 50
                )
            )
        )
    );

    private $fTConfig = array(
        'name' => 'Fields template'
        ,'custom_title' => null
        ,'l1' => 'Fields template'
        ,'l2' => 'Fields template'
        ,'l3' => 'Fields template'
        ,'l4' => 'Fields template'
        ,'template_id' => null //will be set later when processing
        ,'iconCls' => 'icon-snippet'
        ,'type' => 'field'
        ,'fields' => array(
            array(
                'name' => '_title'
                ,'l1' => 'Name'
                ,'l2' => 'Name'
                ,'l3' => 'Name'
                ,'l4' => 'Name'
                ,'type' => 'varchar'
                ,'order' => 0
                ,'cfg' => array(
                    'showIn' => 'top'
                    ,'readOnly' => true
                )
            )
            ,array(
                'name' => 'type'
                ,'l1' => 'Type'
                ,'l2' => 'Type'
                ,'l3' => 'Type'
                ,'l4' => 'Type'
                ,'type' => '_fieldTypesCombo'
                ,'order' => 5
            )
            ,array(
                'name' => 'order'
                ,'l1' => 'Order'
                ,'l2' => 'Order'
                ,'l3' => 'Order'
                ,'l4' => 'Order'
                ,'type' => 'int'
                ,'order' => 6
            )
            ,array(
                'name' => 'cfg'
                ,'l1' => 'Config'
                ,'l2' => 'Config'
                ,'l3' => 'Config'
                ,'l4' => 'Config'
                ,'type' => 'text'
                ,'order' => 7
                ,'cfg' => array(
                    'height' => 100
                )
            )
            ,array(
                'name' => 'solr_column_name'
                ,'l1' => 'Solr column name'
                ,'l2' => 'Solr column name'
                ,'l3' => 'Solr column name'
                ,'l4' => 'Solr column name'
                ,'type' => 'varchar'
                ,'order' => 8
            )
        )
    );

    protected function init()
    {
        echo "init\n";
        $this->DFT = \CB\getOption('DEFAULT_FOLDER_TEMPLATE');
        echo " DFT:".$this->DFT."\n";

        $this->verifyPid();

        /* adjust thesauri template config */
        $this->tTConfig['pid'] = $this->mainPid;
        $this->fTConfig['pid'] = $this->mainPid;

        // $this->thesauriTemplateConfig['template_id'] = $this->mainTemplateId;
        // $this->thesauriTemplateConfig['title_template'] = '{'.\CB\LANGUAGE.'}';

        $i = 1;
        foreach ($GLOBALS['languages'] as $language) {
            $field = array(
                'name' => $language
                ,'l1' => 'Title ('.$language.')'
                ,'l2' => 'Title ('.$language.')'
                ,'l3' => 'Title ('.$language.')'
                ,'l4' => 'Title ('.$language.')'
                ,'type' => 'varchar'
                ,'order' => $i
                // ,'cfg' => array(
                //     'showIn' => 'top'
                // )
            );
            $this->tTConfig['fields'][] = $field;
            $this->fTConfig['fields'][] = $field;
            $i++;
        }
    }

    public function execute()
    {
        $this->prepareExecution();

        /* make some automatic adjustments */

        // before start we'll execute a special procedure that will clear all lost objects.
        // These objects can cause errors on sync templates with tree
        DB\dbQuery('CALL p_clear_lost_objects()') or die(DB\dbQueryError());

        // update possible cyclic references in templates_structure to template_id
        DB\dbQuery('UPDATE templates_structure SET pid = template_id WHERE pid = id') or die(DB\dbQueryError());
        //update header type
        DB\dbQuery("UPDATE templates_structure SET `type` = 'H' WHERE tag = 'H'") or die(DB\dbQueryError());
        //try to set empty names to values from language fields
        DB\dbQuery("UPDATE templates_structure SET `name` = COALESCE(l1, l2, l3, l4, 'unnamed') WHERE (`name` = '')") or die(DB\dbQueryError());
        //update group types
        DB\dbQuery("UPDATE templates_structure SET NAME = 'group', TYPE = 'G' WHERE tag = 'G'") or die(DB\dbQueryError());
        //update max id from tree to avoid id dublication in templates
        $res = DB\dbQuery('SELECT (MAX(id)+1) `max_id` FROM templates_structure') or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            DB\dbQuery('ALTER TABLE `tree` AUTO_INCREMENT='.$r['max_id']) or die(DB\dbQueryError());
        }
        $res->close();
        /* end of make some automatic adjustments */

        //create or update fields template
        $this->fTId = \Util\Templates::createOrUpdateTemplate($this->fTConfig['pid'], $this->fTConfig);
        $this->fTObject = new Objects\Template($this->fTId);
        $data = $this->fTObject->load();
        $data['template_id'] = $this->fTId;
        $this->fTObject->update($data);

        //create or update templates template
        $this->tTId = \Util\Templates::createOrUpdateTemplate($this->tTConfig['pid'], $this->tTConfig);
        $this->tTObject = new Objects\Template($this->tTId);
        $data = $this->tTObject->load();
        $data['template_id'] = $this->tTId;
        $this->tTObject->update($data);

        // now that we've checked/created basic templates -
        // add these items to be available in target folder menu
        echo "Update Menu for target folder ".$this->mainPid."\n";
        Browser\CreateMenu::updateMenuForNode(
            $this->mainPid,
            array(
                $this->tTId
                ,$this->fTId
                ,$this->DFT
            )
        );
        // also set menu to add fields when under a template
        Browser\CreateMenu::updateMenuForTemplate($this->tTId, $this->fTId);
        Browser\CreateMenu::updateMenuForTemplate($this->fTId, $this->fTId);

        echo "loading all templates structure .. \n";
        $this->loadTemplatesStructure();

        echo " Start sync:\n";
        $this->syncStructure($this->rootItems);

        echo "Update references ... \n";
        $this->updateReferences();

        echo "Done\n";

        DB\commitTransaction();
    }

    /**
     * load all tags into an array $this->tags
     * while loading we'll detect root nodes of tags tree
     * and will create associative array of childs
     * @return void
     */
    protected function loadTemplatesStructure()
    {
        $this->items = array();
        $this->rootItems = array();

        /* select all templates structure*/
        $res = DB\dbQuery('SELECT * FROM templates') or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $this->items[$r['id']] = $r;
        }
        $res->close();

        // we have loaded all templates and now we are going to
        // update childs and parent references and detect root nodes
        foreach ($this->items as $id => &$item) {
            if (isset($this->items[$item['pid']])) {
                $parent = &$this->items[$item['pid']];
                $parent['childs'][$item['id']] = &$item;
                $item['parent'] = &$parent;
            } else {
                $this->rootItems[$item['id']] = &$item;
            }
        }

        // now we\'ll load all fields for each template to a similar structure as above for templates

        /* select all templates fields structure*/
        $res = DB\dbQuery('SELECT * FROM templates_structure') or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $this->items[$r['template_id']]['fields'][$r['id']] = $r;
        }
        $res->close();

        foreach ($this->items as $tId => &$template) {
            if (empty($template['fields'])) {
                $template['fields'] = array();
            } else {
                foreach ($template['fields'] as $fId => &$field) {
                    if (isset($template['fields'][$field['pid']])) {
                        $parent = &$template['fields'][$field['pid']];
                        $parent['childs'][$field['id']] = &$field;
                        $field['parent'] = &$parent;
                    } else {
                        $template['rootFields'][$field['id']] = &$field;
                    }
                }
            }
        }
    }

    /**
     * create/update templates in tree by keeping exact structure
     * @return void
     */
    protected function syncStructure(&$nodesArray)
    {
        foreach ($nodesArray as $id => &$node) {
            $pid = isset($node['parent'])
                ? $node['parent']['id']
                : $this->mainPid;

            $data = $this->getTemplateDataForTree($node);

            $data['id'] = \CB\Objects::getChildId($pid, $data['name']);
            $data['pid'] = $pid;

            if (is_null($data['id'])) {
                $data['id'] = $this->genericObject->create($data);
            } else {
                $this->genericObject->update($data);
            }

            $node['oldId'] = $node['id'];
            $node['id'] = $data['id'];

            if ($node['oldId'] != $node['id']) {
                DB\dbQuery(
                    'UPDATE templates
                    SET id = $2
                        ,pid = $3
                    WHERE id = $1',
                    array(
                        $node['oldId']
                        ,$node['id']
                        ,$pid
                    )
                ) or die(DB\dbQueryError());
            }
            if ($node['oldId'] == $this->DFT) {
                $this->DFT = $node['id'];
                $GLOBALS['DFT'] = $node['id'];
            }

            if (!empty($node['childs'])) {
                $this->syncStructure($node['childs']);
            }

            if (!empty($node['rootFields'])) {
                $this->syncFieldsStructure($node['id'], $node['rootFields']);
            }
        }
    }

    protected function syncFieldsStructure($pid, $nodesArray)
    {
        foreach ($nodesArray as $id => &$node) {
            $pid = isset($node['parent'])
                ? $node['parent']['id']
                : $pid;

            $data = $this->getTemplateFieldDataForTree($node);
            // some language fields could be skiped if not present in config
            if (empty($data)) {
                continue;
            }

            $data['id'] = \CB\Objects::getChildId($pid, $node['name']);
            $data['pid'] = $pid;
            if (is_null($data['id'])) {
                $data['id'] = $this->genericObject->create($data);
            } else {
                $this->genericObject->update($data);
            }

            $node['oldId'] = $node['id'];
            $node['id'] = $data['id'];

            if ($node['oldId'] != $node['id']) {
                DB\dbQuery(
                    'UPDATE templates_structure
                    SET id = $2
                        ,pid = $3
                    WHERE id = $1',
                    array(
                        $node['oldId']
                        ,$node['id']
                        ,$pid
                    )
                ) or die(DB\dbQueryError());
            }

            if (!empty($node['childs'])) {
                $this->syncFieldsStructure($node['id'], $node['childs']);
            }
        }
    }

    /**
     * get generic node data for creating in tree from template node properties
     * @param  array $node
     * @return array
     */
    protected function getTemplateDataForTree(&$node)
    {
        $rez = array(
            'name' => $node['name']
            ,'data' => array(
            )
        );
        if (empty($rez['name'])) {
            $rez['name'] = $node['l'.\CB\LANGUAGE_INDEX];
        }

        $rez['data']['_title'] = $rez['name'];

        if ($node['is_folder'] == 1) { //folder
            $rez['template_id'] = $this->DFT;
            //if is folder - assign forcely it's name to l1
            $rez['name'] = $node['l'.\CB\LANGUAGE_INDEX];
            $rez['data']['_title'] = $rez['name'];
        } else { //template
            $rez['template_id'] = $this->tTId;
            foreach ($this->languageFields as $field => $language) {
                if (!empty($node[$field])) {
                    $rez['data'][$language] = $node[$field];
                }
            }
            if (!empty($node['type'])) {
                $rez['data']['type'] = $node['type'];
            }
            if (!empty($node['visible'])) {
                $rez['data']['visible'] = 1;
            }
            if (!empty($node['iconCls'])) {
                $rez['data']['iconCls'] = $node['iconCls'];
            }
            // if (!empty($node['order'])) {
            //     $rez['data']['order'] = $node['order'];
            // }
            if (!empty($node['cfg'])) {
                $rez['data']['cfg'] = $node['cfg'];
            }
            if (!empty($node['title_template'])) {
                $rez['data']['title_template'] = $node['title_template'];
            }
            if (!empty($node['info_template'])) {
                $rez['data']['info_template'] = $node['info_template'];
            }
        }

        return $rez;
    }

    protected function getTemplateFieldDataForTree(&$node)
    {
        $rez = array(
            'name' => $node['name']
            ,'data' => array(
            )
        );

        $rez['template_id'] = $this->fTId;

        foreach ($this->languageFields as $field => $language) {
            if (!empty($node[$field])) {
                $rez['data'][$language] = $node[$field];
            }
        }
        if (empty($rez['name'])) {
            $rez['name'] = $rez['data'][\CB\LANGUAGE];
        }

        $rez['data']['_title'] = $rez['name'];

        if (!empty($node['tag']) && ($node['tag'] == 'H')) {
            $node['type'] = 'H';
        }

        if (!empty($node['type'])) {
            $rez['data']['type'] = $node['type'];
        }

        if (!empty($node['order'])) {
            $rez['data']['order'] = $node['order'];
        }
        if (!empty($node['cfg'])) {
            $rez['data']['cfg'] = $node['cfg'];
        }
        if (!empty($node['solr_column_name'])) {
            $rez['data']['solr_column_name'] = $node['solr_column_name'];
        }

        return $rez;
    }

    protected function updateTemplateStructureReferences()
    {

        echo "update TemplateStructureReferences ...\n";

        $res = DB\dbQuery(
            'SELECT * FROM templates_structure WHERE cfg LIKE \'%template%\''
        ) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $cfg = \CB\Util\toJSONArray($r['cfg']);
            if (!empty($cfg['templates'])) {
                $ids = \CB\Util\toNumericArray($cfg['templates']);
                for ($i=0; $i < sizeof($ids); $i++) {
                    foreach ($this->items as &$template) {
                        if ($ids[$i] == $template['oldId']) {
                            $ids[$i] = $template['id'];
                        }
                    }
                }
                $cfg['templates'] = $ids;
                DB\dbQuery(
                    'UPDATE templates_structure
                    SET cfg = $2
                    WHERE id = $1',
                    array(
                        $r['id']
                        ,json_encode($cfg, JSON_UNESCAPED_UNICODE)
                    )
                ) or die(DB\dbQueryError());
            }
        }
        $res->close();

        echo "update Templates config References ...\n";
        $res = DB\dbQuery(
            'SELECT * FROM templates WHERE cfg LIKE \'%templates%\''
        ) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $cfg = \CB\Util\toJSONArray($r['cfg']);
            if (!empty($cfg['templates'])) {
                $ids = \CB\Util\toNumericArray($cfg['templates']);
                for ($i=0; $i < sizeof($ids); $i++) {
                    foreach ($this->items as &$template) {
                        if ($ids[$i] == $template['oldId']) {
                            $ids[$i] = $template['id'];
                        }
                    }
                }
                $cfg['templates'] = $ids;
                DB\dbQuery(
                    'UPDATE templates
                    SET cfg = $2
                    WHERE id = $1',
                    array(
                        $r['id']
                        ,json_encode($cfg, JSON_UNESCAPED_UNICODE)
                    )
                ) or die(DB\dbQueryError());
            }
        }
        $res->close();
    }

    protected function updateReferences()
    {
        $this->updateMenuAndConfig();
        $this->updateTemplateStructureReferences();
    }

    public function updateMenuAndConfig()
    {
        $config = array();
        $res = DB\dbQuery(
            'SELECT id, param, `value`
             FROM config
            WHERE param LIKE \'%template%\''
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $config[$r['param']] = $r;
        }
        $res->close();

        foreach ($this->items as $fromId => $template) {
            foreach ($config as &$row) {
                $row['value'] = trim(str_replace(','.$fromId.',', ','.$template['id'].',', ','.$row['value'].','), ',');
            }
        }
        /* set Templates to work as directories */
        $ft = explode(',', $config['folder_templates']['value']);
        if (!in_array($this->tTId, $ft)) {
            $ft[] = $this->tTId;
        }
        $config['folder_templates']['value'] = implode(',', $ft);

        foreach ($config as &$row) {
            DB\dbQuery(
                'UPDATE config
                SET `value` = $2
                WHERE id = $1',
                array(
                    $row['id']
                    ,$row['value']
                )
            ) or die(DB\dbQueryError());
        }

        // now update menu
        $menu = array();
        $res = DB\dbQuery(
            'SELECT id, `menu`
             FROM menu'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $menu[] = $r;
        }
        $res->close();

        foreach ($this->items as $fromId => $template) {
            foreach ($menu as &$row) {
                $row['menu'] = trim(str_replace(','.$fromId.',', ','.$template['id'].',', ','.$row['menu'].','), ',');
            }
        }

        foreach ($menu as &$row) {
            DB\dbQuery(
                'UPDATE menu
                SET `menu` = $2
                WHERE id = $1',
                array(
                    $row['id']
                    ,$row['menu']
                )
            ) or die(DB\dbQueryError());
        }
    }
}
