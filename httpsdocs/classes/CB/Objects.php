<?php
namespace CB;

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
            throw new \Exception(L\Wrong_input_data);
        }
        $id = $p['id'];

        // Access check
        if (!Security::canRead($id)) {
            throw new \Exception(L\Access_denied);
        }

        $object = $this->getCustomClassByObjectId($id) or die(L\Wrong_input_data);
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
            ,'title'
            ,'custom_title'
            ,'date'
            ,'date_end'
            ,'pids'
            ,'path'
            ,'cid'
            ,'uid'
            ,'cdate'
            ,'udate'
            ,'case_id'
            ,'data'
        );
        foreach ($properties as $property) {
            if (isset($objectData[$property])) {
                $resultData[$property] = $objectData[$property];
            }
        }

        /* rename some properties for gui */
        $resultData['date_start'] = @$resultData['date'];
        unset($resultData['date']);

        $resultData['pathtext'] = $resultData['path'];
        unset($resultData['path']);

        $resultData['path'] = str_replace(',', '/', $resultData['pids']);
        unset($resultData['pids']);

        // set type property from template
        $objectData['type'] = $templateData['type'];

        global $data;
        // this method is used also internally (by getInfo method),
        // so we skip logging for "load" method in this cases
        if (is_array($data) && ($data['method'] == 'load')) {
            Log::add(array('action_type' => 11, 'object_id' => $id));
        }

        return array('success' => true, 'data' => $resultData);
    }

    /**
     * create an object
     * @param  array $p params
     * @return json  responce
     */
    public function create($p)
    {
        if (empty($p['pid']) || !Security::canCreateActions($p['pid'])) {
            throw new \Exception(L\Access_denied);
        }

        $template = \CB\Templates\SingletonCollection::getInstance()->getTemplate($p['template_id']);
        $templateData = $template->getData();

        $object = $this->getCustomClassByType($templateData['type']);

        //prepare params
        if (empty($p['name'])) {
            $p['name'] = $templateData['title'];
        }
        $p['name'] = $this->getAvailableName($p['pid'], $p['name']);

        $id = $object->create($p);
        Log::add(
            array(
                'action_type' => 8
                ,'object_id' => $id
            )
        );

        $this->createSystemFolders($id, @$templateData['cfg']['system_folders']);

        Solr\Client::runCron();

        return $this->load(array('id' => $id));
    }

    /**
     * save or create an object
     * @param  array $p object properties
     * @return json  responce
     */
    public function save($p)
    {

        $d = json_decode($p['data'], true);

        // check if need to create object instead of update
        if (empty($d['id']) || !is_numeric($d['id'])) {
            return $createData = $this->create($d);
            //$d['id'] = $createData['data']['nid'];
            //return if create method is completed
        }

        // SECURITY: check if current user has write access to this action
        if (!Security::canWrite($d['id'])) {
            throw new \Exception(L\Access_denied);
        }

        /* prepare params */
        if (empty($d['date']) && !empty($d['date_start'])) {
            $d['date'] = $d['date_start'];
        }
        /* end of prepare params */

        // update object
        $object = $this->getCustomClassByObjectId($d['id']);
        $object->update($d);

        Objects::updateCaseUpdateInfo($d['id']);

        Log::add(
            array(
                'action_type' => 9
                ,'object_id' => $d['id']
            )
        );

        /*updating saved document into solr directly (before runing background cron)
            so that it'll be displayed with new name without delay*/
        $solrClient = new Solr\Client();
        $solrClient->updateTree(array('id' => $d['id']));

        //running background cron to index other nodes
        $solrClient->runBackgroundCron();

        return $this->load($d);
    }

    public function queryCaseData($queries)
    {
        $rez = array('success' => true);
        foreach ($queries as $key => $query) {
            $query['pids'] = $query['caseId'];
            switch ($key) {
                case 'properties':
                    /* load general case properties */

                    if (!empty($query['caseId'])) {
                        $case = $this->getCustomClassByObjectId($query['caseId']);
                        $case->load();
                        $rez[$key] = $case->getData();
                        $properties = array();

                        $template = $case->getTemplate();
                        $linearData = $case->getLinearData();

                        foreach ($linearData as $f) {
                            $tf = $template->getField($f['name']);
                            if ($f['name'] == '_title') {
                                continue;
                            }
                            if ($f['name'] == '_date_start') {
                                continue;
                            }
                            $v = $template->formatValueForDisplay($tf, $f['value']);
                            if (is_array($v)) {
                                $v = implode(', ', $v);
                            }
                            $f['value'] = $v;
                            $properties[] = array(
                                'name' => $f['name']
                                ,'title' => $tf['title']
                                ,'type' => $tf['type']
                                ,'cfg' => $tf['cfg']
                                ,'value' => $v
                            );
                        }
                        $rez[$key]['data']['properties'] = $properties;
                    }

                    break;
                case 'actions':
                    $s= new Search();
                    $query['fl'] = 'id,pid,name,type,subtype,date,template_id,oid,cid';
                    if (!empty($GLOBALS['folder_templates'])) {
                        $query['fq'] = '!template_id:('.implode(' OR ', $GLOBALS['folder_templates']).')';
                    }
                    $query['template_types'] = 'object';
                    $query['sort'] = array('date desc');
                    $rez[$key] = $s->query($query);
                    unset($s);
                    break;
                case 'tasks':
                    $s= new Search();
                    $query['fl'] = 'id,name,type,template_id,date,date_end,user_ids,oid,cid';
                    $query['template_types'] = 'task';
                    $query['sort'] = array('date desc');
                    $rez[$key] = $s->query($query);
                    unset($s);
                    break;
                case 'milestones':
                    $rez[$key] = array();
                    break;
            }
        }

        return $rez;
    }

    public function getPreview($id)
    {
        $rez = array();
        if (!is_numeric($id)) {
            return;
        }

        // SECURITY: check if current user has at least read access to this case
        if (!Security::canRead($id)) {
            throw new \Exception(L\Access_denied);
        }

        $top = '';
        $body = '';
        $bottom = '';
        try {
            $obj = $this->getCustomClassByObjectId($id);
            $obj->load();
            $linearData = $obj->getLinearData();
        } catch (\Exception $e) {
            return '';
        }

        $template = $obj->getTemplate();
        $gf = array();
        //group fields in display blocks
        foreach ($linearData as $field) {
            $tf = $template->getField($field['name']);
            if (empty($tf['cfg'])) {
                $group = 'body';
            } elseif (@$tf['cfg']['showIn'] == 'top') {
                $group = 'top';
            } elseif (@$tf['cfg']['showIn'] == 'tabsheet') {
                $group = 'bottom';
            } else {
                $group = 'body';
            }
            $field['tf'] = $tf;
            $gf[$group][] = $field;
        }

        if (!empty($gf['top'])) {
            foreach ($gf['top'] as $f) {
                if ($f['name'] == '_title') {
                    continue;
                }
                if ($f['name'] == '_date_start') {
                    continue;
                }
                $v = $template->formatValueForDisplay($f['tf'], $f['value']);
                if (is_array($v)) {
                    $v = implode(', ', $v);
                }
                if (!empty($v)) {
                    $top .= '<tr><td class="prop-key">'.$f['tf']['title'].'</td><td class="prop-val">'.$v.'</td></tr>';
                }
            }
        }
        if (!empty($gf['body'])) {
            foreach ($gf['body'] as $f) {
                $v = $template->formatValueForDisplay($f['tf'], @$f['value']);
                if (is_array($v)) {
                    $v = implode('<br />', $v);
                }

                if (empty($v) && empty($f['info']) && empty($f['files'])) {
                    continue;
                }
                $body .= '<tr><td'.(empty($f['tf']['level']) ? '' : ' style="padding-left: '.($f['tf']['level'] * 10).'px"').
                    ' class="prop-key">'.$f['tf']['title'].'</td><td class="prop-val">'.$v.
                    (empty($f['info']) ? '' : '<p class="prop-info">'.$f['info'].'</p>').'</td></tr>';
            }
        }

        $tmp = Files::getFilesBlockForPreview($id);
        if (!empty($tmp)) {
            $bottom .= '<div class="obj-preview-h pt10">'.L\Files.'</div>'.$tmp.'<br />';
        }
        $tmp = Tasks::getActiveTasksBlockForPreview($id);
        if (!empty($tmp)) {
            $bottom .= '<div class="obj-preview-h pt10">'.L\ActiveTasks.'</div>'.$tmp.'<br />';
        }

        if (!empty($gf['bottom'])) {
            foreach ($gf['bottom'] as $f) {
                $v = $template->formatValueForDisplay($f['tf'], $f['value']);
                if (empty($v)) {
                    continue;
                }
                $bottom .=  '<div class="obj-preview-h">'.$f['tf']['title'].'</div>'.$v.'<br />';
            }
        }

        if (!empty($data['tasks'])) {
            $d = array();
            foreach ($data['tasks'] as $t) {
                $info = $t['owner'];
                if ($t['responsible_user_ids'] != $t['cid']) {
                    /* showing users list */
                    $info .= ' &rarr; '.implode(', ', array_values($t['users']));
                }
                $small_fields = array();
                if (!empty($t['completed'])) {
                    $small_fields[] = L\Accomplished_date.': '.Util\formatMysqlDate($t['completed']);
                }
                $info .= ((empty($info) || empty($small_fields)) ? '' : '<br />').implode(', ', $small_fields);

                if (!empty($info)) {
                    $info = '<br /><span class="fs11 cG">'.$info.'</span>';
                }
                $d[] = '<tr><td><a class="task click" nid="'.$t['id'].'">'.$t['title'].'</a>'.$info.'</td><td>'.Util\formatMysqlDate($t['cdate']).'</td><td>'.Util\formatMysqlDate($t['date_end']);
            }
            if (!empty($d)) {
                $bottom .= '<table border="0" cellpadding="2" width="100%" style="padding: 5px 0px; border-bottom: 1px solid lightgray">'.
                '<tr class="bgcLG cG"><th width="20%" class="icon-padding icon-calendar-task">'.L\Tasks.
                '</th><th width="25%">'.L\Created.'</th><th width="30%">'.
                L\Deadline.'</th></tr><tr>'.implode('</tr><tr>', $d).'</tr></table>';
            }
        }
        /* end of tasks */

        Log::add(array('action_type' => 12, 'object_id' => $id ));
        if (!empty($top)) {
            $top = '<div class="obj-preview-h">'.L\Details.'</div>'.$top;
        }
        $top .= $body;
        if (!empty($top)) {
            $top = '<table class="obj-preview">'.$top.'</table><br />';
        }

        return '<div style="padding:10px">'.$top.$bottom.'</div>';
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
                throw new \Exception(L\Access_denied);
            }

            /* select distinct associated case ids from the case */
            $obj = new Objects\Object($p['id']);
            $obj->load();
            $template = $obj->getTemplate();
            $linearData = $obj->getLinearData();
            foreach ($linearData as $f) {
                $tf = $template->getField($f['name']);
                if ($tf['type'] == '_objects') {
                    $a = Util\toNumericArray(@$f['value']);
                    $ids = array_merge($ids, $a);
                }
            }
        } else {
            $template = new Objects\Template($p['template_id']);
            $template->load();
        }

        if ($template) {
            $templateData = $template->getData();
            foreach ($templateData['fields'] as $field) {
                if (!empty($field['cfg']['value'])) {
                    $a = Util\toNumericArray($field['cfg']['value']);
                    $ids = array_merge($ids, $a);
                }
            }
        }

        $ids = array_unique($ids);
        if (empty($ids)) {
            return array('success' => true, 'data' => array());
        }

        /* end of select distinct case ids from the case */
        $res = DB\dbQuery(
            'SELECT DISTINCT t.id
                ,t.`name`
                ,t.date
                ,t.`type`
                ,t.subtype
                ,t.template_id
                ,t2.status
            FROM tree t
            LEFT JOIN tasks t2 ON t.id = t2.id
            WHERE t.id IN ('.implode(', ', $ids).')
            ORDER BY 2'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            if (!empty($r['date'])) {
                $r['date'][10] = 'T';
                $r['date'] .= 'Z';
            }
            $data[] = $r;
        }
        $res->close();

        return array('success' => true, 'data' => $data);
    }

    /**
     * create system foldes for an object
     * @param  int   $object_id
     * @param  array $folderIds
     * @return void
     */
    public function createSystemFolders($object_id, $folderIds)
    {
        $folderIds = Util\toNumericArray($folderIds);
        if (empty($folderIds)) {
            return;
        }
        $childs = array();
        foreach ($folderIds as $folderId) {
            array_push(
                $childs,
                array(
                    'pid' => $object_id
                    ,'tag_id' => $folderId
                )
            );
        }

        $pid = $object_id;

        while (!empty($childs)) {
            $node = array_shift($childs);
            /*get tag name & type*/
            $res = DB\dbQuery(
                'SELECT l'.USER_LANGUAGE_INDEX.' `title`, `type` FROM tags WHERE id = $1',
                $node['tag_id']
            ) or die( DB\dbQueryError() );

            if ($r = $res->fetch_assoc()) {
                $node['name'] = $r['title'];
                $node['type'] = $r['type'];
                $node['template_id'] = CONFIG\DEFAULT_FOLDER_TEMPLATE;
            }
            $res->close();
            /* end of get tag name & type*/
            if ($node['type'] == 1) {
                $rez = $this->create($node);
                $pid = $rez['data']['id'];
            } else {
                $pid = $node['pid'];
            }

            /* checking if childs exist for added folder and append them to childs table */
            $res = DB\dbQuery(
                'SELECT id FROM tags WHERE pid = $1',
                $node['tag_id']
            ) or die( DB\dbQueryError() );

            while ($r = $res->fetch_assoc()) {
                $childs[] = array('pid' => $pid, 'tag_id' => $r['id']);
            }
            $res->close();
            /* end of checking if childs exist for added folder and append them to childs table */
        }
    }

    /**
     * set additional data to be saved in solr
     * @param  reference $object_record
     * @return void
     */
    public static function getSolrData(&$object_record)
    {
        $obj = Objects::getCustomClassByObjectId($object_record['id']);
        $objData = $obj->load();
        $linearData = $obj->getLinearData();
        $template = $obj->getTemplate();

        $object_record['content'] = '';

        foreach ($linearData as $f) {
            $field = $template->getField($f['name']);
            $processed_values = array();
            if (!empty($f['value'])) {
                /* make changes to value if needed */
                switch ($field['type']) {
                    case 'boolean':
                    case 'checkbox':
                        $f['value'] = empty($f['value']) ? false : true;
                        break;
                    case 'date':
                        $f['value'] .= 'Z';
                        if (@$f['value'][10] == ' ') {
                            $f['value'][10] = 'T';
                        }
                        break;
                    case 'combo':
                    case 'popuplist':
                        $f['value'] = Util\toNumericArray($f['value']);
                        if (empty($f['value'])) {
                            break;
                        }
                        $sres = DB\dbQuery(
                            'SELECT l'.LANGUAGE_INDEX.' `title`
                            FROM tags
                            WHERE id IN ('.implode(',', $f['value']).')'
                        ) or die(DB\dbQueryError());

                        while ($sr = $sres->fetch_assoc()) {
                            $processed_values[] = $sr['title'];
                        }
                        $sres->close();
                        break;
                    case 'html':
                        $f['value'] = strip_tags($f['value']);
                        break;
                }
                /* make changes to value if needed */

                if (@$field['cfg']['faceting'] && in_array($field['type'], array('combo', 'int', '_objects'))) {
                    $solr_field = $field['solr_column_name'];
                    if (empty($solr_field)) {
                        $solr_field = ( empty($field['cfg']['source']) || ($field['cfg']['source'] == 'thesauri') ) ?
                            'sys_tags' : 'tree_tags';
                    }
                    $arr = Util\toNumericArray($f['value']);
                    foreach ($arr as $v) {
                        if (empty($object_record[$solr_field]) || !in_array($v, $object_record[$solr_field])) {
                            $object_record[$solr_field][] = $v;
                        }
                    }
                }
            }

            if (!empty($f['value'])) {
                if (!empty($processed_values)) {
                    foreach ($processed_values as $v) {
                        $object_record['content'] .= $field['title'].' '.$v."\n";
                    }
                } elseif (!is_array($f['value'])) {
                    $object_record['content'] .= $field['title'].' '.
                        (in_array($field['solr_column_name'], array('date_start', 'date_end', 'dates')) ?
                            substr($f['value'], 0, 10): $f['value'])."\n";
                }
            }
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
            $res = DB\dbQuery(
                'SELECT template_id FROM tree WHERE id = $1',
                $objectId
            ) or die(DB\dbQueryError());
            if ($r = $res->fetch_assoc()) {
                $tc = Templates\SingletonCollection::getInstance();
                Cache::set($var_name, $tc->getType($r['template_id']));
            }
            $res->close();
        }

        return Cache::get($var_name);
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

        $id = null;
        $i = 1;
        do {
            $res = DB\dbQuery(
                'SELECT id
                FROM tree
                WHERE pid = $1
                    AND name = $2
                    AND dstatus = 0',
                array(
                    $pid
                    ,$newName
                )
            ) or die(DB\dbQueryError());

            if ($r = $res->fetch_assoc()) {
                $id = $r['id'];
            } else {
                $id = null;
            }
            $res->close();

            if (!empty($id)) {
                $newName = $name.' ('.$i.')'.( empty($ext) ? '' : '.'.$ext);
            }
            $i++;
        } while (!empty($id));

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
}
