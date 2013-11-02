<?php
namespace CB\Objects;

use CB\DB as DB;

/**
 * class for generic casebox objects
 */

class Object
{
    /**
     * object id
     * @var int
     */
    protected $id = null;

    /**
     * variable used to load template for this object
     * @var boolean
     */
    public $loadTemplate = true;

    /**
     * object template
     * @var CB\Template object
     */
    protected $template = null;

    /**
     * object data
     * @var array
     */
    protected $data = array();

    public function __construct($id = null, $loadTemplate = true)
    {
        if (is_numeric($id)) {
            $this->id = $id;
        }
        $this->loadTemplate = $loadTemplate;
    }

    /**
     * create an object with specified params
     * @param  array $p object properties
     * @return int   created id
     */
    public function create($p = false)
    {
        if ($p !== false) {
            if (isset($p['id'])) {
                if (is_numeric($p['id'])) {
                    $this->id = $p['id'];
                } else {
                    $this->id = null;
                    unset($p['id']);
                }
            }

            $this->data = $p;

            $this->template = null;
            if (!empty($this->data['template_id']) && $this->loadTemplate) {
                $this->template = \CB\Templates\SingletonCollection::getInstance()->getTemplate($this->data['template_id']);
            }
        }

        $p = &$this->data;

        // check input params
        if (!isset($p['pid'])) {
            throw new \Exception("No pid specified for object creation", 1);
        }

        if (empty($p['name'])) {
            throw new \Exception("No name specified for object creation", 1);
        }

        // we admit object creation without template id
        // if (empty($p['template_id']) || !is_numeric($p['template_id'])) {
        //     throw new \Exception("No template_id specified for object creation", 1);
        // }

        if (empty($p['pid'])) {
            $p['pid'] = null;
        }

        if (empty($p['tag_id'])) {
            $p['tag_id'] = null;
        }

        \CB\fireEvent('beforeNodeDbCreate', $p);

        DB\dbQuery(
            'INSERT INTO tree (
                pid
                ,name
                ,template_id
                ,cid
                ,tag_id
                ,updated)
            VALUES ($1, $2, $3, $4, $5, 1)',
            array(
                $p['pid']
                ,$p['name']
                ,$p['template_id']
                ,$_SESSION['user']['id']
                ,$p['tag_id']
            )
        ) or die(DB\dbQueryError());

        $this->id = DB\dbLastInsertId();
        $p['id'] = $this->id;

        $this->createCustomData();

        \CB\fireEvent('nodeDbCreate', $p);

        return $this->id;
    }

    /**
     * internal function used by create method for creating custom data
     * @return void
     */
    protected function createCustomData()
    {
        $p = &$this->data;

        DB\dbQuery(
            'INSERT INTO objects (
                id
                ,`title`
                ,cid)
            VALUES($1, $2, $3)
            ON DUPLICATE KEY
            UPDATE `title` = $2',
            array(
                $this->id
                ,$p['name']
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        /* set internal title field for object if present in its template */
        DB\dbQuery(
            'INSERT INTO objects_data (
                object_id
                ,field_id
                ,value)
            SELECT $1
                 , id
                 , $2
            FROM templates_structure
            WHERE template_id = $3
                AND name = \'_title\'
            ON DUPLICATE KEY
            UPDATE value = $2',
            array(
                $this->id
                ,$p['name']
                ,$p['template_id']
            )
        ) or die(DB\dbQueryError());
    }

    /**
     * load object data into $this->data
     * @param  int   $id
     * @return array loaded data
     */
    public function load($id = null)
    {
        if (!is_numeric($id)) {
            if (!is_numeric($this->id)) {
                throw new \Exception("No object id specified for load", 1);
            }
            $id = $this->id;
        } else {
            $this->id = $id;
        }

        $this->data = array();
        $this->template = null;

        $res = DB\dbQuery(
            'SELECT t.*
                ,ti.pids
                ,ti.path
                ,ti.case_id
                ,ti.acl_count
                ,ti.security_set_id
            FROM tree t
            JOIN tree_info ti
                ON t.id = ti.id
            WHERE t.id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $this->data = $r;

            // ($this->data['template_id'] != $this->id)
            if (!empty($this->data['template_id']) && $this->loadTemplate) {
                $this->template = \CB\Templates\SingletonCollection::getInstance()->getTemplate($this->data['template_id']);
            }
        }
        $res->close();

        $this->loadCustomData();

        return $this->data;
    }

    /**
     * load custom data for $this->id
     *
     * in this partucular case, for objects, this method sets
     * gridData into $this->data
     * @return void
     */
    protected function loadCustomData()
    {
        /* load custom data from objects table */
        $res = DB\dbQuery(
            'SELECT title
                ,custom_title
                ,iconCls
                ,private_for_user
            FROM objects
            WHERE id = $1',
            $this->id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $this->data = array_merge($this->data, $r);
        }
        $res->close();

        /* load grid data */
        $rez = array();
        // managers can see private for user data
        $canManage = \CB\Security::canManage();

        $res = DB\dbQuery(
            'SELECT concat(\'f\', field_id, \'_\', duplicate_id) field
                ,id
                ,`value`
                ,info
                ,files
                ,private_for_user `pfu`
            FROM objects_data
            WHERE object_id = $1',
            $this->id
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $field = $r['field'];
            unset($r['field']);
            if (empty($r['pfu']) || ($r['pfu'] == $_SESSION['user']['id']) || $canManage) {
                $rez['values'][$field] = $r;
            } else {
                $rez['hideFields'][] = $field;
            }
        }
        $res->close();

        $res = DB\dbQuery(
            'SELECT id
                ,pid
                ,field_id
            FROM objects_duplicates
            WHERE object_id = $1
            ORDER BY id',
            $this->id
        ) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $rez['duplicateFields'][$r['field_id']][$r['id']] = $r['pid'];
        }
        $res->close();

        $this->data['gridData'] = $rez;

        return $rez;
    }

    /**
     * update object
     * @param  array   $p optional properties. If not specified then $this-data is used
     * @return boolean
     */
    public function update($p = false)
    {
        if ($p !== false) {
            $this->data = $p;
            if (isset($p['id'])) {
                $this->id = $p['id'];
            }
            $this->template = null;
            if (!empty($this->data['template_id']) && $this->loadTemplate) {
                $this->template = \CB\Templates\SingletonCollection::getInstance()->getTemplate($this->data['template_id']);
            }
        }

        if (!is_numeric($this->id)) {
            throw new \Exception("No object id specified for update", 1);
        }

        \CB\fireEvent('beforeNodeDbUpdate', $this->data);

        $tableFields = array(
            'pid'
            ,'user_id'
            ,'system'
            ,'template_id'
            ,'tag_id'
            ,'target_id'
            ,'name'
            ,'date'
            ,'date_end'
            ,'size'
            ,'is_main'
            ,'cfg'
            ,'inherit_acl'
            ,'oid'
            ,'did'
            ,'dstatus'
        );
        $saveFields = array();
        $saveValues = array($this->id, $_SESSION['user']['id']);
        $params = array('`uid` = $2', '`udate` = CURRENT_TIMESTAMP', 'updated = 1');
        $i = 3;
        foreach ($tableFields as $fieldName) {
            if (isset($p[$fieldName]) && ($p[$fieldName] !== 'id')) {
                $saveFields[] = $fieldName;
                $saveValues[] = (is_scalar($p[$fieldName]) || is_null($p[$fieldName]))
                    ? $p[$fieldName]
                    : json_encode($p[$fieldName]);
                $params[] = "`$fieldName` = \$$i";
                $i++;
            }
        }
        if (!empty($saveFields)) {
            DB\dbQuery(
                'UPDATE tree
                SET '.implode(',', $params).'
                WHERE id = $1',
                $saveValues
            ) or die(DB\dbQueryError());
        }

        $this->updateCustomData();

        \CB\fireEvent('nodeDbUpdate', $this->data);

        return true;
    }

    /**
     * update objects custom data
     * @return boolean
     */
    protected function updateCustomData()
    {
        $d = &$this->data;

        if (!empty($this->template)) {
            $templateData = $this->template->getData();
        }

        $fields = array();

        /* save object duplicates from grid */
        $duplicate_ids = array(0 => 0);
        if (isset($d['gridData']['duplicateFields'])) {
            $sql = 'INSERT INTO objects_duplicates
                    (pid
                    ,object_id
                    ,field_id)
                VALUES ($1, $2, $3)';
            foreach ($d['gridData']['duplicateFields'] as $field_id => $fv) {
                $i = 0;
                foreach ($fv as $duplicate_id => $duplicate_pid) {
                    if (!is_numeric($duplicate_id)) {
                        DB\dbQuery(
                            $sql,
                            array(
                                $duplicate_ids[$duplicate_pid]
                                ,$d['id']
                                ,$field_id
                            )
                        ) or die(DB\dbQueryError());
                        $duplicate_ids[$duplicate_id] = DB\dbLastInsertId();
                    } else {
                        $duplicate_ids[$duplicate_id] = $duplicate_id;
                    }
                    $fields[$field_id]['duplicates'][$i]['id'] = $duplicate_id;
                    $i++;
                }
            }
        }
        // delete unused duplicate ids
        DB\dbQuery(
            'DELETE
            FROM objects_duplicates
            WHERE object_id = $1
                AND (id NOT IN ('.implode(', ', array_values($duplicate_ids)).'))
                '.(
                    \CB\Security::isAdmin() // filter secure fields
                    ? ''
                    : ' AND id NOT IN
                        (SELECT duplicate_id
                         FROM objects_data
                         WHERE object_id = $1
                             AND duplicate_id <> 0
                             AND private_for_user <> '.$_SESSION['user']['id'].'
                        )'
            ),
            $d['id']
        ) or die(DB\dbQueryError());
        /* end of save object duplicates from grid */

        /* save object values from grid */
        $ids = array(0);
        if (isset($d['gridData'])) {
            foreach ($d['gridData']['values'] as $f => $fv) {
                if (!isset($fv['value'])) {
                    $fv['value'] = null;
                }
                if (!isset($fv['info'])) {
                    $fv['info'] = null;
                }
                if (isset($fv['pfu']) && empty($fv['pfu'])) {
                    $fv['pfu'] = null;
                }
                $f = explode('_', $f);
                $field_id = substr($f[0], 1);

                if (empty($field_id)) {
                    continue;
                }

                $duplicate_id = intval($duplicate_ids[$f[1]]);
                $duplicate_index = 0;
                if (isset($fields[$field_id]['duplicates'])) {
                    foreach ($fields[$field_id]['duplicates'] as $k => $v) {
                        if (!empty($v['id']) && is_array($v['id'])) {
                            if ($v['id'] == $duplicate_id) {
                                $fields[$field_id]['duplicates'][$k]['index'] = $duplicate_index;
                            } else {
                                $duplicate_index++;
                            }
                        }
                    }
                }

                if (!is_scalar($fv['value']) && !is_null($fv['value'])) {
                    $fv['value'] = json_encode($fv['value']);
                }

                @$params = array(
                    $d['id']
                    ,$field_id
                    ,$duplicate_id
                    ,$fv['value']
                    ,$fv['info']
                    ,$fv['files']
                    ,$fv['pfu']
                );
                DB\dbQuery(
                    'INSERT INTO objects_data
                        (object_id
                        ,field_id
                        ,duplicate_id
                        ,`value`
                        ,info
                        ,files
                        ,private_for_user)
                    VALUES ($1
                        ,$2
                        ,$3
                        ,$4
                        ,$5
                        ,$6
                        ,$7) ON DUPLICATE KEY
                    UPDATE
                        id = last_insert_id(id)
                        ,object_id = $1
                        ,field_id = $2
                        ,duplicate_id = $3
                        ,`value` = $4
                        ,info = $5
                        ,files = $6
                        ,private_for_user = $7',
                    $params
                ) or die(DB\dbQueryError());
                array_push($ids, DB\dbLastInsertId());
            }
        }

        DB\dbQuery(
            'DELETE
            FROM objects_data
            WHERE object_id = $1
                AND (id NOT IN ('.implode(', ', $ids).'))
                '.(
                    \CB\Security::isAdmin() // filter secure fields
                    ? ''
                    : ' AND ((private_for_user is null)
                            OR (private_for_user = '.$_SESSION['user']['id'].')
                            )'
            ),
            $d['id']
        ) or die(DB\dbQueryError());

        // prepare params
        if (empty($d['title'])) {
            $d['title'] = ucfirst($this->getAutoTitle());
        }

        if (empty($d['custom_title'])) {
            $d['custom_title'] = $this->getFieldValue('_title');
        }

        if (empty($d['date'])) {
            $d['date'] = $this->getFieldValue('_date_start');
        }

        if (empty($d['date_end'])) {
            $d['date_end'] = $this->getFieldValue('_date_end');
        }

        // updating object properties into the db  /*(empty($object_iconCls) ? '' : ', iconCls = $7')/**/
        @DB\dbQuery(
            'UPDATE objects
            SET title = $2
                ,custom_title = $3
                ,date_start = $4
                ,date_end = $5
                ,iconCls = $6
                ,private_for_user = $7
                ,uid = $8
                ,udate = CURRENT_TIMESTAMP
            WHERE id = $1',
            array(
                $d['id']
                ,$d['title']
                ,$d['custom_title']
                ,$d['date']
                ,$d['date_end']
                ,$templateData['iconCls']
                ,@$d['pfu']
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());
        /* end of updating object properties into the db */

        return true;
    }

    /**
     * get a field value from current objects data ($this->data)
     * @param  int | varchar $field          field name or its id
     * @param  integer       $duplication_id optional. default 0
     * @return variant
     */
    public function getFieldValue($field, $duplication_id = 0)
    {
        if (empty($this->template)) {
            return null;
        }
        $field = $this->template->getField($field);
        if (empty($field)) {
            return null;
        }

        $field_index = 'f'.$field['id'].'_'.$duplication_id;
        if (empty($this->data['gridData']['values'])) {
            return null;
        }
        $values = &$this->data['gridData']['values'];
        if (!empty($values[$field_index])) {
            return $values[$field_index]['value'];
        }

        return null;
    }

    /**
     * return auto generated title for curent object data
     * @return varchar
     */
    protected function getAutoTitle()
    {
        if (empty($this->template)) {
            return;
        }
        $rez = '';
        $templateData = $this->template->getData();

        $rez = str_replace(
            array(
                '{template_title}'
                ,'{phase_title}'
            ),
            array(
                $templateData['title']
                , ''/*$phase['name']/**/
            ),
            $templateData['title_template']
        );

        /* replace field values */
        if (!empty($this->data['gridData']['values'])) {
            foreach ($this->data['gridData']['values'] as $fk => $fv) {
                $field_id = explode('_', $fk);
                $field_id = substr(array_shift($field_id), 1);
                $field = $this->template->getField($field_id);
                $v = $this->template->formatValueForDisplay($field, $fv['value'], 'text');
                if (is_array($v)) {
                    $v = implode(',', $v);
                }
                $rez = str_replace('{f'.$field_id.'}', $v, $rez);
                $rez = str_replace('{'.$field['name'].'}', $v, $rez);
                $rez = str_replace('{'.$field['name'].'_info}', @$fv['info'], $rez);
            }
        }

        //replacing field titles into object title variable
        foreach ($templateData['fields'] as $fk => $fv) {
            $rez = str_replace('{f'.$fk.'t}', $fv['title'], $rez);

        }
        // evaluating the title if contains php code
        if (strpos($rez, '<?php') !== false) {
            @eval(' ?>'.$rez.'<?php ');
            if (!empty($title)) {
                $rez = $title;
            }
        }
        //replacing any remained field placeholder from the title
        $rez = preg_replace('/\{[^\}]+\}/', '', $rez);
        $rez = stripslashes($rez);

        return $rez;
    }

    /**
     * get object data
     *
     * @return array object properties
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * set object data
     *
     * @param array $data template properties
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * get object template property
     *
     * @return array object properties
     */
    public function getTemplate()
    {
        if (empty($this->template) && $this->loadTemplate) {
            $this->template = \CB\Templates\SingletonCollection::getInstance()->getTemplate($this->data['template_id']);
        }

        return $this->template;
    }

    /**
     * copy an object to $pid or over $targetId
     *
     * better way to copy an object over another one is to delete the target,
     * but this could be very dangerous. We could delete required/important data
     * so i suggest to just mark overwriten object with dstatus = 3.
     * But in this situation appears another problem with child nodes.
     * Childs should be moved to new parent.
     *
     * @param  int $pid      if not specified then will be set to pid of targetId
     * @param  int $targetId
     * @return int the id of copied object
     */
    public function copyTo($pid = false, $targetId = false)
    {
        // check input params
        if (!is_numeric($this->id) ||
            (!is_numeric($pid) && !is_numeric($targetId))
        ) {
            return false;
        }

        /* security check */
        if (!\CB\Security::canRead($this->id)) {
            return false;
        }
        /* end of security check */

        if (is_numeric($targetId)) {
            /* target security check */
            if (!\CB\Security::canWrite($targetId)) {
                return false;
            }
            /* end of target security check */
            // marking overwriten object with dstatus = 3
            DB\dbQuery(
                'UPDATE tree SET updated = 1, dstatus = 3, did = $2 WHERE id = $1',
                array($targetId, $_SESSION['user']['id'])
            ) or die(DB\dbQueryError());

            //get pid from target if not specified
            $res = DB\dbQuery(
                'SELECT pid FROM tree WHERE id = $1',
                $targetId
            ) or die(DB\dbQueryError());

            if ($r = $res->fetch_assoc()) {
                $pid = $r['pid'];
            }
            $res->close();
        } else {
            /* pid security check */
            if (!\CB\Security::canWrite($pid)) {
                return false;
            }
            /* end of pid security check */
        }

        /* check again if we have pid set
            It can be unset when not existent $targetId is specified
        */
        if (!is_numeric($pid)) {
            return false;
        }

        // copying the object to $pid

        DB\dbQuery(
            'INSERT INTO `tree`
                (`id`
                ,`old_id`
                ,`pid`
                ,`user_id`
                ,`system`
                ,`type`
                ,`subtype`
                ,`template_id`
                ,`tag_id`
                ,`target_id`
                ,`name`
                ,`date`
                ,`date_end`
                ,`size`
                ,`is_main`
                ,`cfg`
                ,`inherit_acl`
                ,`acl_count`
                ,`cid`
                ,`cdate`
                ,`uid`
                ,`udate`
                ,`updated`
                ,`oid`
                ,`did`
                ,`ddate`
                ,`dstatus`)
            SELECT
                NULL
                ,`old_id`
                ,$2
                ,`user_id`
                ,`system`
                ,`type`
                ,`subtype`
                ,`template_id`
                ,`tag_id`
                ,`target_id`
                ,`name`
                ,`date`
                ,`date_end`
                ,`size`
                ,`is_main`
                ,`cfg`
                ,`inherit_acl`
                ,0
                ,`cid`
                ,`cdate`
                ,$3
                ,CURRENT_TIMESTAMP
                ,1
                ,`oid`
                ,`did`
                ,`ddate`
                ,`dstatus`
            FROM `tree` t
            WHERE id = $1',
            array(
                $this->id
                ,$pid
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        $objectId = DB\dbLastInsertId();

        /* we have now object created, so we star copy all its possible data:
            - tree_info is filled automaticly by trigger
            - custom security rules from tree_acl
            - custom object data
        */

        // copy node custom security rules if set
        DB\dbQuery(
            'INSERT INTO `tree_acl`
            (`node_id`
            ,`user_group_id`
            ,`allow`
            ,`deny`
            ,`cid`
            ,`cdate`
            ,`uid`
            ,`udate`)
            SELECT
                $2
                ,`user_group_id`
                ,`allow`
                ,`deny`
                ,`cid`
                ,`cdate`
                ,`uid`
                ,`udate`
            FROM `tree_acl`
            WHERE node_id = $1',
            array(
                $this->id
                ,$objectId
            )
        ) or die(DB\dbQueryError());

        $this->copyCustomDataTo($objectId);

        // move childs from overwriten targetId (which has been marked with dstatus = 3)
        // to newly copied object
        if (is_numeric($targetId)) {
            DB\dbQuery(
                'UPDATE tree SET updated = 1, pid = $2 WHERE pid = $1 AND dstatus = 0',
                array($targetId, $this->id)
            ) or die(DB\dbQueryError());
        }

        return $objectId;
    }

    protected function copyCustomDataTo($targetId)
    {
        // - objects, objects_data and objects_duplicates

        // copy data from objects table
        DB\dbQuery(
            'INSERT INTO `objects`
                (`id`
                ,`title`
                ,`custom_title`
                ,`date_start`
                ,`date_end`
                ,`iconCls`
                ,`private_for_user`
                ,`cid`
                ,`cdate`
                ,`uid`
                ,`udate`)
            SELECT
                $2
                ,`title`
                ,`custom_title`
                ,`date_start`
                ,`date_end`
                ,`iconCls`
                ,`private_for_user`
                ,`cid`
                ,`cdate`
                ,$3
                ,CURRENT_TIMESTAMP
            FROM `objects`
            WHERE id = $1',
            array(
                $this->id
                ,$targetId
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        /* copy data from objects_duplicates table
         We have to copy records from objects_duplicates firstly
         because duplicate ids will change */
        $oldDuplicates = array();
        $newDuplicateIds = array( '0' => 0);

        $res = DB\dbQuery(
            'SELECT
                `id`
                ,`pid`
                ,`object_id`
                ,`field_id`
            FROM `objects_duplicates`
            WHERE object_id = $1 ORDER BY id',
            $this->id
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $oldDuplicates[$r['id']] = $r;
        }
        $res->close();
        foreach ($oldDuplicates as $duplicate_id => $values) {
            DB\dbQuery(
                'INSERT INTO `objects_duplicates`
                    (`pid`
                    ,`object_id`
                    ,`field_id`)
                VALUES ($1, $2, $3)',
                array(
                    $values['pid']
                    ,$targetId
                    ,$values['field_id']
                )
            ) or die(DB\dbQueryError());
            $newDuplicateIds[$duplicate_id] = DB\dbLastInsertId();
        }

        // now update all old pids for duplicates to new generated ids
        foreach ($oldDuplicates as $duplicate_id => $values) {
            if ($values['pid'] == 0) {
                continue;
            }
            DB\dbQuery(
                'UPDATE objects_duplicates
                SET pid = $2
                WHERE id = $1',
                array(
                    $newDuplicateIds[$duplicate_id]
                    ,$newDuplicateIds[$values['pid']]
                )
            ) or die(DB\dbQueryError());
        }
        /* end of copy data from objects_duplicates table */

        // copy data from objects_data table.
        $objectData = array();
        $res = DB\dbQuery(
            'SELECT
                `field_id`
                ,`duplicate_id`
                ,`value`
                ,`info`
                ,`files`
                ,`private_for_user`
            FROM `objects_data`
            WHERE object_id = $1',
            $this->id
        ) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $objectData[] = $r;
        }
        $res->close();

        foreach ($objectData as $r) {
            DB\dbQuery(
                'INSERT INTO `objects_data`
                    (`object_id`
                    ,`field_id`
                    ,`duplicate_id`
                    ,`value`
                    ,`info`
                    ,`files`
                    ,`private_for_user`)
                VALUES($1, $2, $3, $4, $5, $6, $7)',
                array(
                    $targetId
                    ,$r['field_id']
                    ,'0'.@$newDuplicateIds[$r['duplicate_id']]
                    ,$r['value']
                    ,$r['info']
                    ,$r['files']
                    ,$r['private_for_user']
                )
            ) or die(DB\dbQueryError());
        }
    }

    /**
     * move an object to $pid or over $targetId
     *
     * we'll use the same principle as for copy
     *
     * @param  int $pid      if not specified then will be set to pid of targetId
     * @param  int $targetId
     * @return int the id of moved object or false
     */
    public function moveTo($pid = false, $targetId = false)
    {
        // check input params
        if (!is_numeric($this->id) ||
            (!is_numeric($pid) && !is_numeric($targetId))
        ) {
            return false;
        }

        /* security check */
        if (!\CB\Security::canDelete($this->id)) {
            return false;
        }
        /* end of security check */

        if (is_numeric($targetId)) {
            /* target security check */
            if (!\CB\Security::canWrite($targetId)) {
                return false;
            }
            /* end of target security check */
            // marking overwriten object with dstatus = 3
            DB\dbQuery(
                'UPDATE tree SET updated = 1, dstatus = 3, did = $2 WHERE id = $1',
                array($targetId, $_SESSION['user']['id'])
            ) or die(DB\dbQueryError());

            //get pid from target if not specified
            $res = DB\dbQuery(
                'SELECT pid FROM tree WHERE id = $1',
                $targetId
            ) or die(DB\dbQueryError());

            if ($r = $res->fetch_assoc()) {
                $pid = $r['pid'];
            }
            $res->close();
        } else {
            /* pid security check */
            if (!\CB\Security::canWrite($pid)) {
                return false;
            }
            /* end of pid security check */
        }

        /* check again if we have pid set
            It can be unset when not existent $targetId is specified
        */
        if (!is_numeric($pid)) {
            return false;
        }

        // moving the object to $pid

        DB\dbQuery(
            'UPDATE tree set updated = 1, pid = $2 where id = $1',
            array($this->id, $pid)
        ) or die(DB\dbQueryError());

        // move childs from overwriten targetId (which has been marked with dstatus = 3)
        // to newly copied object
        if (is_numeric($targetId)) {
            DB\dbQuery(
                'UPDATE tree SET updated = 1, pid = $2 WHERE pid = $1 AND dstatus = 0',
                array($targetId, $this->id)
            ) or die(DB\dbQueryError());
        }

        return $this->id;
    }
}
