<?php
namespace CB\Objects;

use CB\DB as DB;
use CB\Util as Util;

/**
 * class for generic casebox objects
 */

class Object extends OldObject
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
            if (array_key_exists('id', $p)) {
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

        \CB\fireEvent('beforeNodeDbCreate', $this);

        DB\dbQuery(
            'INSERT INTO tree (
                id
                ,pid
                ,name
                ,template_id
                ,cid
                ,tag_id
                ,updated)
            VALUES ($1, $2, $3, $4, $5, $6, 1)',
            array(
                $this->id
                ,$p['pid']
                ,$p['name']
                ,$p['template_id']
                ,$_SESSION['user']['id']
                ,$p['tag_id']
            )
        ) or die(DB\dbQueryError());

        $this->id = DB\dbLastInsertId();
        $p['id'] = $this->id;

        $this->createCustomData();

        \CB\fireEvent('nodeDbCreate', $this);

        return $this->id;
    }

    /**
     * internal function used by create method for creating custom data
     * @return void
     */
    protected function createCustomData()
    {
        $p = &$this->data;

        $p['data'] = Util\toJSONArray(@$p['data']);

        $this->verifyAutoUpdatedParams();

        DB\dbQuery(
            'INSERT INTO objects (
                id
                ,`title`
                ,`custom_title`
                ,date_start
                ,date_end
                ,`data`
                ,cid)
            VALUES($1, $2, $3, $4, $5, $6, $7)
            ON DUPLICATE KEY
            UPDATE `title` = $2
                    ,`custom_title` = $3
                    ,`date_start` = $4
                    ,`date_end` = $5
                    ',
            array(
                $this->id
                ,@$p['title']
                ,@$p['custom_title']
                ,@$p['date']
                ,@$p['date_end']
                ,json_encode($p['data'], JSON_UNESCAPED_UNICODE)
                ,$_SESSION['user']['id']
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
     * data into $this->data
     * @return void
     */
    protected function loadCustomData()
    {
        /* load custom data from objects table */
        $res = DB\dbQuery(
            'SELECT title
                ,custom_title
                ,data
                ,iconCls
                ,private_for_user
            FROM objects
            WHERE id = $1',
            $this->id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            if (!empty($r['data'])) {
                $r['data'] = json_decode($r['data'], true);
            }
            $this->data = array_merge($this->data, $r);
        }
        $res->close();

        /* if data is null then this object has not been converted to new structure.
            Load Old data an convert it to new format
        */
        if ((!isset($this->data['data']) || is_null($this->data['data'])) && $this->loadTemplate) {
            $this->loadOldGridDataToNewFormat();
        }
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
            if (array_key_exists('id', $p)) {
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

        \CB\fireEvent('beforeNodeDbUpdate', $this);

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
                    : json_encode($p[$fieldName], JSON_UNESCAPED_UNICODE);
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

        \CB\fireEvent('nodeDbUpdate', $this);

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

        $this->verifyAutoUpdatedParams();

        if (empty($d['data'])) {
            $d['data'] = array();
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
                ,`data` = $8
                ,uid = $9
                ,udate = CURRENT_TIMESTAMP
            WHERE id = $1',
            array(
                $d['id']
                ,@$d['title']
                ,@$d['custom_title']
                ,$d['date']
                ,@$d['date_end']
                ,$templateData['iconCls']
                ,@$d['pfu']
                ,json_encode($d['data'], JSON_UNESCAPED_UNICODE)
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());
        /* end of updating object properties into the db */

        return true;
    }

    /**
     * delete an object from tree or marks it as deleted
     * @param boolean $permanent Specify true to delete the object permanently.
     *                            Default to false.
     * @return void
     */
    public function delete($permanent = false)
    {
        \CB\fireEvent('beforeNodeDbDelete', $this);

        if ($permanent) {
            DB\dbQuery(
                'DELETE from tree WHERE id = $1',
                $this->id
            ) or die(DB\dbQueryError());
            $solrClient = new \CB\Solr\Client();
            $solrClient->deleteByQuery('id:'.$this->id);
        } else {
            DB\dbQuery(
                'UPDATE tree
                SET did = $2
                    ,dstatus = 1
                    ,ddate = CURRENT_TIMESTAMP
                    ,updated = (updated | 1)
                WHERE id = $1',
                array(
                    $this->id
                    ,$_SESSION['user']['id']
                )
            ) or die(DB\dbQueryError());
            DB\dbQuery(
                'CALL p_mark_all_childs_as_deleted($1, $2)',
                array(
                    $this->id
                    ,$_SESSION['user']['id']
                )
            ) or die(DB\dbQueryError());
        }

        $this->deleteCustomData($permanent);

        \CB\fireEvent('nodeDbDelete', $this);
    }

    /**
     * delete custom data for an object
     *
     * use this method (overwrite it) for descendant classes
     * when there is need to delete custom data on object delete
     * @return coid
     */
    protected function deleteCustomData()
    {

    }

    protected function verifyAutoUpdatedParams()
    {
        $p = &$this->data;

        $autoTitle = ucfirst($this->getAutoTitle());
        if (!empty($autoTitle)) {
            $p['title'] = $autoTitle;
        }

        $titleField = @$this->getFieldValue('_title', 0)['value'];
        if (!empty($titleField)) {
            $p['custom_title'] = $titleField;
        }

        if (empty($p['title']) && empty($p['custom_title'])) {
            $p['custom_title'] = $p['name'];
        }

        $dateStart = @$this->getFieldValue('_date_start', 0)['value'];
        if (!empty($dateStart)) {
            $p['date'] = $dateStart;
        }

        $dateEnd = @$this->getFieldValue('_date_end', 0)['value'];
        if (!empty($dateEnd)) {
            $p['date_end'] = $dateEnd;
        }

    }

    /**
     * get a field value from current objects data ($this->data)
     *
     * This function return an array of values for duplicate fields
     *
     * @param  varchar $fieldName  field name
     * @param  integer $valueIndex optional value duplication index. default false
     * @return array
     */
    public function getFieldValue($fieldName, $valueIndex = false)
    {
        $rez = array();
        $ld = $this->getAssocLinearData();

        if (!empty($ld[$fieldName])) {
            $rez = $ld[$fieldName];
        }
        if ($valueIndex !== false) {
            $rez = @$rez[$valueIndex];
        }

        return $rez;
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
        $fields = array();//used from php templates of title
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

        $ld = $this->getLinearData();
        /* replace field values */
        foreach ($ld as $field) {
            $tf = $this->template->getField($field['name']);
            $v = $this->template->formatValueForDisplay($tf, $field['value'], false);
            if (is_array($v)) {
                $v = implode(',', $v);
            }
            $v = addcslashes($v, '\'');
            $rez = str_replace('{'.$field['name'].'}', $v, $rez);
            $fields[$field['name']] = $v;
        }

        //replacing field titles into object title variable
        foreach ($templateData['fields'] as $fk => $fv) {
            $rez = str_replace('{f'.$fv['name'].'t}', $fv['title'], $rez);

        }
        // evaluating the title if contains php code
        if (strpos($rez, '<?php') !== false) {
            eval(' ?>'.$rez.'<?php ');
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
     * get linear array of properties of object properties
     *
     * @param array $data template properties
     */
    public function getLinearData()
    {
        $rez = $this->getLinearNodesData($this->data['data']);

        return $rez;
    }

    /**
     * get an associative linear array of field values
     *
     * @param array $data template properties
     */
    public function getAssocLinearData()
    {
        $rez = array();
        $linearData = $this->getLinearData();
        foreach ($linearData as $field) {
            $value = array_intersect_key($field, array('value' => 1, 'info' => 1, 'files' => 1));
            $rez[$field['name']][] = $value;
        }

        return $rez;
    }

    protected function getLinearNodesData(&$data)
    {
        $rez = array();
        foreach ($data as $fieldName => $fieldValue) {
            if ($this->isFieldValue($fieldValue)) {
                $fieldValue = array($fieldValue);
            }

            foreach ($fieldValue as $fv) {
                $value = array('name' => $fieldName);
                if (is_scalar($fv) ||
                    is_null($fv)
                ) {
                    $value['value'] = $fv;
                } else {
                    $value = array_merge($value, $fv);
                }
                $rez[] = $value;
                if (!empty($fv['childs'])) {
                    $rez = array_merge($rez, $this->getLinearNodesData($fv['childs']));
                }
            }
        }

        return $rez;
    }

    /**
     * set object data
     *
     * @param array $data template properties
     */
    public function setData($data)
    {
        $this->data = $data;
        if (array_key_exists('id', $data)) {
            $this->id = $data['id'];
        }
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
     * detect if a given value is a generic field value
     * from json array stored in data fields
     *
     * @param  variant $value
     * @return boolean
     */
    public static function isFieldValue($value)
    {
        if (is_scalar($value) ||
            is_null($value)
        ) {
            return true;
        }
        // analize array values
        if (is_array($value)) {
            // non associative array
            if (array_values($value) === $value) {
                return false;
            } else { //associative array
                $keys = array_keys($value);
                $diff = array_diff($keys, array('name', 'value', 'info', 'files', 'childs'));

                return empty($diff);
            }
        }

        // not detected case;
        return null;
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

        /* we have now object created, so we start copy all its possible data:
            - tree_info is filled automaticly by trigger
            - custom security rules from tree_acl
            - custom object data
        */

        // copy node custom security rules if set
        \CB\Security::copyNodeAcl($this->id, $objectId);

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
                ,`data`
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
                ,`data`
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
