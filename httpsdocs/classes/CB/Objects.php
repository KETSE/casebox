<?php
namespace CB;

use CB\Util;

class Objects
{
    /**
     * load object and return json responce
     * @param  array $p array containing id of object
     * @return json  responce
     */
    public function load($p)
    {
        $rez = array();

        // check if object id is numeric
        if (!is_numeric($p['id'])) {
            throw new \Exception(L\get('Wrong_input_data'));
        }
        $id = $p['id'];

        // Access check
        if (!Security::canRead($id)) {
            throw new \Exception(L\get('Access_denied'));
        }
        $object = $this->getCustomClassByObjectId($id) or die(L\get('Wrong_input_data'));

        $object->load();
        $objectData = $object->getData();

        $template = $object->getTemplate();
        $templateData = $template->getData();

        $resultData = array();

        /* select only required properties for result */
        $properties = array(
            'id'
            ,'pid'
            ,'template_id'
            ,'name'
            ,'date'
            ,'date_end'
            ,'pids'
            ,'path'
            ,'cid'
            ,'uid'
            ,'cdate'
            ,'udate'
            ,'case_id'
            ,'status'
            ,'data'
            ,'can'
        );
        foreach ($properties as $property) {
            if (isset($objectData[$property])) {
                $resultData[$property] = $objectData[$property];
            }
        }

        /* rename some properties for gui */
        $resultData['date_start'] = @$resultData['date'];
        unset($resultData['date']);

        $arr = array(&$resultData);

        $pids = explode(',', $resultData['pids']);
        array_pop($pids);
        $resultData['path'] = implode('/', $pids);

        Search::setPaths($arr);
        $resultData['pathtext'] = $resultData['path'];

        $resultData['path'] = str_replace(',', '/', $resultData['pids']);

        unset($resultData['pids']);

        // set type property from template
        $objectData['type'] = $templateData['type'];

        return array(
            'success' => true
            ,'data' => $resultData
            ,'menu' => Browser\CreateMenu::getMenuForPath($p['id'])
        );
    }

    /**
     * create an object
     * @param  array $p params
     * @return json  responce
     */
    public function create($p)
    {
        $pid = empty($p['pid'])
            ? @$p['path']
            : $p['pid'];
        if (empty($pid)) {
            throw new \Exception(L\get('Access_denied'));
        }

        $p['pid'] = Path::detectRealTargetId($pid);

        //security check moved inside objects class

        $template = \CB\Templates\SingletonCollection::getInstance()->getTemplate($p['template_id']);
        $templateData = $template->getData();

        $object = $this->getCustomClassByType($templateData['type']);

        //prepare params
        if (empty($p['name'])) {
            $p['name'] = $templateData['title'];
        }
        $p['name'] = $this->getAvailableName($p['pid'], $p['name']);

        $id = $object->create($p);

        Solr\Client::runCron();

        $rez = $this->load(array('id' => $id));
        $rez['data']['isNew'] = true;

        return $rez;
    }

    /**
     * save or create an object
     * @param  array $p object properties
     * @return json  responce
     */
    public function save($p)
    {

        $d = Util\toJSONArray($p['data']);

        // check if need to create object instead of update
        if (empty($d['id']) || !is_numeric($d['id'])) {
            return $this->create($d);
        }

        // SECURITY: check if current user has write access to this action
        if (!Security::canWrite($d['id'])) {
            throw new \Exception(L\get('Access_denied'));
        }

        /* prepare params */
        if (empty($d['date']) && !empty($d['date_start'])) {
            $d['date'] = $d['date_start'];
        }
        /* end of prepare params */

        // update object
        $object = $this->getCachedObject($d['id']);

        //set sys_data from object, it can contain custom data
        //that shouldn't be overwritten
        $d['sys_data'] = $object->getSysData();

        $object->update($d);

        Objects::updateCaseUpdateInfo($d['id']);

        /*updating saved document into solr directly (before runing background cron)
            so that it'll be displayed with new name without delay*/
        $solrClient = new Solr\Client();
        $solrClient->updateTree(array('id' => $d['id']));

        //running background cron to index other nodes
        $solrClient->runBackgroundCron();

        return $this->load($d);
    }

    /**
     * getting preview for an item
     * @param  int id
     * @return array array of divided preview per common and complex fields
     */
    public static function getPreview($id)
    {
        if (!is_numeric($id)) {
            return;
        }

        // SECURITY: check if current user has at least read access to this case
        if (!Security::canRead($id)) {
            throw new \Exception(L\get('Access_denied'));
        }

        try {
            $obj = static::getCachedObject($id);
        } catch (\Exception $e) {
            return '';
        }

        return $obj->getPreviewBlocks();
    }

    /**
     * get the list of objects referenced inside another object
     * @param  array | int $p params
     * @return json        response
     */
    public static function getAssociatedObjects($p)
    {
        $data = array();

        if (is_numeric($p)) {
            $p = array('id' => $p);
        }

        if (empty($p['id']) && empty($p['template_id'])) {
            return array(
                'success' => true
                ,'data' => $data
                ,'s'=>'1'
            );
        }

        $ids = array();

        $template = null;

        if (!empty($p['id'])) {
            // SECURITY: check if current user has at least read access to this case
            if (!Security::canRead($p['id'])) {
                throw new \Exception(L\get('Access_denied'));
            }

            /* select distinct associated case ids from the case */
            $obj = new Objects\Object($p['id']);
            $obj->load();
            $template = $obj->getTemplate();
            $linearData = $obj->getLinearData();
            foreach ($linearData as $f) {
                $tf = $template->getField($f['name']);
                if ($tf['type'] == '_objects') {
                    $a = Util\toIntArray(@$f['value']);
                    $ids = array_merge($ids, $a);
                }
            }
        } else {
            $template = new Objects\Template($p['template_id']);
            $template->load();

        }

        if (!empty($p['data']) && is_array($p['data'])) {
            foreach ($p['data'] as $key => $value) {
                $a = Util\toIntArray($value);
                $ids = array_merge($ids, $a);
            }
        }

        if ($template) {
            $templateData = $template->getData();
            foreach ($templateData['fields'] as $field) {
                if (!empty($field['cfg']['value'])) {
                    $a = Util\toIntArray($field['cfg']['value']);
                    $ids = array_merge($ids, $a);
                }
            }
        }

        $ids = array_unique($ids);
        if (empty($ids)) {
            return array('success' => true, 'data' => array());
        }

        /* end of select distinct case ids from the case */

        $data = Search::getObjects($ids, 'id,template_id,name,date,status:task_status');
        $data = array_values($data);

        return array('success' => true, 'data' => $data);
    }

    /**
     * set additional data to be saved in solr
     * @param  reference $object_record
     * @return void
     */
    public static function getSolrData(&$object_record)
    {
        $obj = Objects::getCachedObject($object_record['id']);

        if (empty($obj)) {
            return;
        }

        $linearData = $obj->getLinearData();
        $template = $obj->getTemplate();

        $objData = $obj->getData();

        //specific check for comments
        //comments may be long and can exceed name field definition
        // to be reviewed and moved in comments class
        if (@$object_record['template_type'] == 'comment') {
            if (!empty($objData['data']['_title'])) {
                $object_record['content'] = $objData['data']['_title'];

            }

            return;
        }

        $object_record['content'] = empty($objData['name'])
            ? ''
            : $objData['name']."\n";

        // add target type for shortcut items
        if (!empty($objData['target_type'])) {
            $object_record['target_type'] = $objData['target_type'];
        }
        // add last comment info if present
        if (!empty($objData['sys_data']['lastComment'])) {
            $object_record['comment_user_id'] = $objData['sys_data']['lastComment']['user_id'];
            $object_record['comment_date'] = $objData['sys_data']['lastComment']['date'];
        }

        $field = array();
        foreach ($linearData as $f) {
            if (is_object($template)) {
                $field = $template->getField($f['name']);
            }

            if (!empty($f['value'])) {
                /* make changes to value if needed */
                switch ($field['type']) {
                    case 'boolean':
                    case 'checkbox':
                        $f['value'] = empty($f['value']) ? false : true;
                        break;

                    case 'date':
                    case 'datetime':
                        if (!empty($f['value'])) {
                            //check if there is only date, without time
                            if (strlen($f['value']) == 10) {
                                $f['value'] .= 'T00:00:00';
                            }

                            if (substr($f['value'], -1) != 'Z') {
                                $f['value'] .= 'Z';
                            }

                            if (@$f['value'][10] == ' ') {
                                $f['value'][10] = 'T';
                            }
                        }
                        break;

                    case 'time':
                        if (!empty($f['value'])) {
                            $a = explode(':', $f['value']);
                            @$f['value'] = $a[0] * 3600 + $a[1] * 60 + $a[2];
                        }
                        break;

                    case 'html':
                        $f['value'] = strip_tags($f['value']);
                        break;
                }
                /* make changes to value if needed */

                if (@$field['cfg']['faceting']) {
                    Objects::setCustomSOLRfields($object_record, $field, @$f['value']);
                }
            }

            // adding value to content field
            if (!empty($f['value'])) {
                $object_record['content'] .= $field['title'].' '.
                    (in_array($field['solr_column_name'], array('date_start', 'date_end', 'dates'))
                        ? substr($f['value'], 0, 10)
                        : $f['value']
                    )."\n";
            }
        }
    }

    /**
     * set custom SOLR columns
     * @param reference $object_records
     *         reference $field
     * @return void
     */
    public static function setCustomSOLRfields(&$object_record, &$field, $value)
    {
        // is field stored in custom SOLR column?
        if (!@$field['cfg']['faceting']) {
            return;
        }

        $solr_field = $field['solr_column_name'];

        // is SOLR field specified?
        if (empty($solr_field)) {
            // warn that SOLR field is missing
            \CB\debug("Field '" . $field['name'] . "' is faceted but solr_column_name is missing");

            return;
        }

        switch ($field['type']) {
            # 'combo', 'int', 'objects' fields
            case 'combo':
            case 'int':
            case '_objects':

                $arr = Util\toNumericArray($value);
                $val = Util\toNumericArray(@$object_record[$solr_field]);

                foreach ($arr as $v) {
                    if (empty($val) || !in_array($v, $val)) {
                        $val[] = $v;
                    }
                }

                if (empty($val)) {
                    unset($object_record[$solr_field]);

                } elseif (is_array($val) && (sizeof($val) < 2)) {//set just value if 1 element array
                    $object_record[$solr_field] = array_shift($val);

                } else {
                    $object_record[$solr_field] = $val;
                }

                break;

            case 'varchar':
                // storing value in SOLR without any changes (TODO: think if the value should be cleaned/transformed)
                // we assume values are checked before inserted into DB.
                // maybe to strip_tags at least?
                $object_record[$solr_field] = $value;
                break;

            case 'date':
            case 'datetime':
                if (!empty($value)) {
                    $object_record[$solr_field] = $value;
                }
                break;
        }

    }

    /**
     * set additional data to be saved in solr for multiple records
     * @param  reference $object_records
     * @return void
     */
    public static function getBulkSolrData(&$object_records)
    {

        foreach ($object_records as $object_id => &$object_record) {
            if (in_array(@$object_record['template_type'], array('case', 'object', 'email'))) {
                Objects::getSolrData($object_record);
            }
        }
    }

    /**
     * updates udate and uid for a case
     * @param  int  $case_or_caseObject_id
     * @return void
     */
    public static function updateCaseUpdateInfo($case_or_caseObject_id)
    {
        DB\dbQuery(
            'UPDATE tree
            SET uid = $2
                ,udate = CURRENT_TIMESTAMP
            WHERE id = (select case_id from tree_info where id = $1)',
            array(
                $case_or_caseObject_id
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());
    }

    //--------------------- new refactoring methods

    /**
     * get pids of a given object id
     * @param  int   $objectId
     * @return array
     */
    public static function getPids($objectId)
    {
        $rez = array();

        if (!is_numeric($objectId)) {
            return $rez;
        }

        $res = DB\dbQuery(
            'SELECT pids FROM tree_info WHERE id = $1',
            $objectId
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = Util\toNumericArray($r['pids']);

            //exclude itself from pids
            array_pop($rez);
        }
        $res->close();

        return $rez;
    }

    /**
     * get template id of an object
     * @param  int      $objectId
     * @return int|null
     */
    public static function getTemplateId($objectId)
    {
        $rez = null;
        if (!is_numeric($objectId)) {
            return $rez;
        }

        $res = DB\dbQuery(
            'SELECT template_id FROM tree WHERE id = $1',
            $objectId
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $rez = $r['template_id'];
        }
        $res->close();

        return $rez;
    }
    /**
     * get template type of an object
     * @param  int          $objectId
     * @return varchar|null
     */
    public static function getType($objectId)
    {
        if (!is_numeric($objectId)) {
            return null;
        }

        $var_name = 'obj_template_type'.$objectId;

        if (!Cache::exist($var_name)) {
            $tc = Templates\SingletonCollection::getInstance();
            Cache::set($var_name, $tc->getType(self::getTemplateId($objectId)));
        }

        return Cache::get($var_name);
    }

    /**
     * get name for an object id from database
     * Note: for multilanguage to work Search::getObjectNames() should be used
     * @param  int          $objectId
     * @return varchar|null
     */
    public static function getName($objectId)
    {
        $rez = null;

        if (!is_numeric($objectId)) {
            return $rez;
        }

        $res = DB\dbQuery(
            'SELECT name FROM tree WHERE id = $1',
            $objectId
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['name'];
        }

        return $rez;
    }

    /**
     * get an object from cache or loads id and store in cache
     * @param  int    $id
     * @return object
     */
    public static function getCachedObject($id)
    {
        //verify if already have cached result
        $var_name = 'Objects['.$id.']';
        if (\CB\Cache::exist($var_name)) {
            return \CB\Cache::get($var_name);
        }
        $obj = static::getCustomClassByObjectId($id);
        if (!empty($obj)) {
            $obj->load();
            \CB\Cache::set($var_name, $obj);
        }

        return $obj;
    }

    /**
     * get an instance of the class designed for objectId (based on it's template type)
     * @param  int    $objectId
     * @return object
     */
    public static function getCustomClassByObjectId($objectId)
    {
        $type = Objects::getType($objectId);

        return Objects::getCustomClassByType($type, $objectId);
    }

    /**
     * get an instance of the class designed for specified type
     * @param  varchar $type
     * @param  int     $objectId
     * @return object
     */
    public static function getCustomClassByType($type, $objectId = null)
    {
        if (empty($type)) {
            return null;
        }

        switch ($type) {
            case 'file':
                return new Objects\File($objectId);
                break;
            case 'task':
                return new Objects\Task($objectId);
                break;
            case 'template':
                return new Objects\Template($objectId);
                break;
            case 'field':
                return new Objects\TemplateField($objectId);
                break;
            case 'comment':
                return new Objects\Comment($objectId);
                break;
            case 'shortcut':
                return new Objects\Shortcut($objectId);
                break;
            default:
                return new Objects\Object($objectId);
                break;
        }
    }

    /**
     * copy an unknown object to a $pid or over a $targetId
     * @param  int $objectId
     * @param  int $pid
     * @param  int $targetId
     * @return int new copied object id
     */
    public function copy($objectId, $pid = false, $targetId = false)
    {
        $class = $this->getCustomClassByObjectId($objectId);
        $data = $class->load();
        $data['id'] = $targetId;
        $data['pid'] = $pid;

        $rez = $targetId;

        if ($targetId === false) {
            $rez = $class->create($data);
        } else {
            $class->update($data);
        }

        return $rez;
    }

    /**
     * move an unknown object to a $pid or over a $targetId
     * @param  int $objectId
     * @param  int $pid
     * @param  int $targetId
     * @return int new moved object id
     */
    public function move($objectId, $pid = false, $targetId = false)
    {
        $class = $this->getCustomClassByObjectId($objectId);

        return $class->moveTo($pid, $targetId);
    }

    /**
     * get a new name, that does not exist under specified $pid
     *
     * If there is no any active (not deleted) object with specied name under $pid
     * then same name is returned.
     * If name exists then a new name will be generated with " (<number>)" at the end.
     * Note that extension is not changed.
     * Extension is considered any combination of chars delimited by dot
     * at the end of an object and its length is less than 5 chars.
     *
     * @param  int     $pid  parent id
     * @param  varchar $name desired name
     * @return varchar new name
     */
    public static function getAvailableName($pid, $name)
    {
        $newName = $name;
        $a = explode('.', $name);
        $ext = '';
        if ((sizeof($a) > 1) && (sizeof($a) < 5)) {
            $ext = array_pop($a);
        }
        $name = implode('.', $a);

        /* get similar names*/
        $names = array();
        $res = DB\dbQuery(
            'SELECT name
            FROM tree
            WHERE pid = $1
                AND name like $2
                AND dstatus = 0',
            array(
                $pid
                ,$name . '%' . '.'.$ext
            )
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $names[] = $r['name'];
        }
        $res->close();

        $i = 1;
        while (in_array($newName, $names)) {
            $newName = $name.' ('.$i.')'.( empty($ext) ? '' : '.'.$ext);
            $i++;
        };

        return $newName;
    }

    /**
     * checks if given id exists in our tree
     * @param  int     $id
     * @return boolean
     */
    public static function idExists($id)
    {
        $rez = false;
        if (empty($id)) {
            return $rez;
        }
        $res = DB\dbQuery('SELECT id FROM tree WHERE id = $1', $id) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $rez = true;
        }
        $res->close();

        return $rez;
    }

    /**
     * get basic info for a given object id
     * @param  int  $id
     * @return json responce
     */
    public static function getBasicInfoForId($id)
    {
        $rez = array(
            'success' => false
            ,'id' => $id
            ,'data' => array()
        );

        if (empty($id) || !is_numeric($id)) {
            return $rez;
        }

        $res = DB\dbQuery(
            'SELECT t.id
                ,t.name
                ,t.`system`
                ,t.`type`
                ,ti.pids
                ,t.`template_id`
                ,tt.`type` template_type
            FROM tree t
            JOIN tree_info ti on t.id = ti.id
            LEFT JOIN templates tt ON t.template_id = tt.id
            WHERE t.id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez['success'] = true;
            $rez['data'] = $r;
        }
        $res->close();

        return $rez;
    }

    /**
     * get a child node id by its name under specified $pid
     * @param  int      $id
     * @param  varchar  $name
     * @return int|null
     */
    public static function getChildId($pid, $name)
    {
        $rez = null;
        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE pid = $1
                AND name = $2
                AND dstatus = 0',
            array(
                $pid
                ,$name
            )
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['id'];
        }
        $res->close();

        return $rez;
    }

    /**
     * get data for defined plugins to be displayed in properties panel for selected object
     * @param  array $p remote properties containing object id
     * @return ext   direct responce
     */
    public function getPluginsData($p)
    {
        $id = @$p['id'];
        $templateId = @$p['template_id'];
        $template = null;
        $templateData = null;
        $objectPlugins = null;

        $rez = array(
            'success' => false
            ,'data' => array()
        );
        if ((empty($id) && empty($templateId)) ||
            (!is_numeric($id) && !is_numeric($templateId))
        ) {
            return $rez;
        }

        if (is_numeric($id)) {
            if (!$this->idExists($id)) {
                return $rez;
            }

            if (!Security::canRead($id)) {
                throw new \Exception(L\get('Access_denied'));
            }

            $rez['menu'] = Browser\CreateMenu::getMenuForPath($id);

            /* now we'll try to detect plugins config that could be found in following places:
                1. in config of the template for the given object, named object_plugins
                2. in core config, property object_type_plugins (config definitions per available template type values: object, case, task etc)
                3. a generic config,  named default_object_plugins, could be defined in core config
            */

            $o = $this->getCachedObject($id);

            if (!empty($o)) {
                $template = $o->getTemplate();
                if (!empty($template)) {
                    $templateData = $template->getData();
                }
            }
        } else {
            $id = null;
            $templates = Templates\SingletonCollection::getInstance();
            $templateData = $templates->getTemplate($templateId)->getData();
        }

        $from = empty($p['from'])
            ? ''
            : $p['from'];

        if (!empty($from)) {
            if (isset($templateData['cfg']['object_plugins'])) {
                $op = $templateData['cfg']['object_plugins'];

                if (!empty($op[$from])) {
                    $objectPlugins = $op[$from];
                } else {
                    //check if config has only numeric keys, i.e. plugins specified directly (without a category)
                    if (!Util\isAssocArray($op)) {
                        $objectPlugins = $op;
                    } else {
                        $objectPlugins = Config::getObjectTypePluginsConfig(@$templateData['type'], $from);
                    }
                }
            }
        }

        if (empty($objectPlugins)) {
            if (!empty($templateData['cfg']['object_plugins'])) {
                $objectPlugins = $templateData['cfg']['object_plugins'];
            } else {
                $objectPlugins = Config::getObjectTypePluginsConfig($templateData['type'], $from);
            }
        }

        $rez['success'] = true;

        if (empty($objectPlugins)) {
            return $rez;
        }

        foreach ($objectPlugins as $pluginName) {
            $class = '\\CB\\Objects\\Plugins\\'.ucfirst($pluginName);
            $pClass = new $class($id);
            $prez = $pClass->getData();

            $rez['data'][$pluginName] = $prez;
        }

        return $rez;
    }

    /**
     * add comments for an objects
     * @param array $p input params (id, msg)
     */
    public function addComment($p)
    {
        $rez = array('success' => false);
        if (empty($p['id']) || !is_numeric($p['id']) || empty($p['msg'])) {
            $rez['msg'] = L\get('Wrong_input_data');

            return $rez;
        }

        if (!Security::canRead($p['id'])) {
            throw new \Exception(L\get('Access_denied'));
        }

        $commentTemplates = Templates::getIdsByType('comment');
        if (empty($commentTemplates)) {
            $rez['msg'] = 'No comment templates found';

            return $rez;
        }

        $co = new Objects\Comment();

        $data = array(
            'pid' => $p['id']
            ,'draftId' => @$p['draftId']
            ,'template_id' => array_shift($commentTemplates)
            ,'system' => 2
            ,'data' => array(
                '_title' => $p['msg']
            )
        );

        $id = $co->create($data);

        Solr\Client::runCron();

        return array(
            'success' => true
            ,'data' => \CB\Objects\Plugins\Comments::loadComment($id)
        );
    }

    /**
     * update own comment
     * @param array $p input params (id, msg)
     */
    public function updateComment($p)
    {
        $rez = array('success' => false);

        if (empty($p['id']) || !is_numeric($p['id']) || empty($p['text'])) {
            $rez['msg'] = L\get('Wrong_input_data');

            return $rez;
        }

        $comment = static::getCustomClassByObjectId($p['id']);
        $commentData = $comment->load();
        if ($commentData['cid'] == $_SESSION['user']['id']) {
            $commentData['data']['_title'] = $p['text'];
            $comment->update($commentData);

            Solr\Client::runCron();

            $rez = array(
                'success' => true
                ,'data' => \CB\Objects\Plugins\Comments::loadComment($commentData['id'])
            );

        }

        return $rez;
    }

    /**
     * remove own comment
     * @param array $p input params (id)
     */
    public function removeComment($p)
    {
        $rez = array('success' => false);

        if (empty($p['id']) || !is_numeric($p['id'])) {
            $rez['msg'] = L\get('Wrong_input_data');

            return $rez;
        }

        $comment = static::getCustomClassByObjectId($p['id']);
        $commentData = $comment->load();

        if ($commentData['cid'] == $_SESSION['user']['id']) {
            $comment->delete();

            Solr\Client::runCron();

            $rez['success'] = true;
        }

        return $rez;
    }
}
