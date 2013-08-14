<?php
namespace CB;

class Objects
{
    public function load($p)
    {
        /* procedure for loading all necessary properties of a given case object */
        $rez = array();
        $d = $p->data; //shortcut

        if (!is_numeric($d->id)) {
            throw new \Exception(L\Wrong_input_data);
        }
        // SECURITY: check if object id is numeric
        if (!Security::isAdmin() && !Security::canRead($d->id)) {
            throw new \Exception(L\Access_denied);
        }
        // end of SECURITY: check if object id is numeric

        $template = $this->getTemplateInfo(null, $d->id);
        $rez['template_id'] = $template['id'];
        $rez['template_pid'] = $template['pid'];
        $rez['iconCls'] = $template['iconCls'];
        $rez['type'] = $template['type'];

        $rez['id'] = $d->id;

        if ($rez['template_pid'] == 5 || $rez['template_pid'] == 6) {
            $rez['spentTime'] = array();
            $rez['tasks'] = array();
        }

        /* get object title */
        $res = DB\dbQuery(
            'SELECT t.pid
                        ,t.case_id
                        ,o.title
                        ,o.custom_title
                        ,t.name
                        ,o.date_start
                        ,o.date_end
                        ,o.author
                        ,o.private_for_user `pfu`
                        ,(o.date_end < now()) is_active
                       ,files_count
                       , f_get_tree_ids_path(t.pid) `path`
                       , f_get_tree_path(t.id) `pathtext`
                       , t.cdate
                       , t.udate
                       , t.cid
                       , t.uid
            FROM objects o
            JOIN tree t ON o.id = t.id
            WHERE o.id = $1',
            array(
                $d->id
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = array_merge($rez, $r);
        }
        $res->close();
        /* end of get object title */

        $rez['gridData'] = Templates::getObjectsData($d->id);

        /* get Tasks */
        $sql = 'SELECT DISTINCT
                    t.id
                    ,t.title
                    ,t.description
                    ,t.`date_end`
                    ,t.cdate
                    ,t.responsible_user_ids
                    ,t.responsible_party_id
                    ,t.cid
                    ,t.completed
                    ,(SELECT l'.USER_LANGUAGE_INDEX.'
                        FROM tags
                        WHERE id = t.responsible_party_id) responsible_party
                FROM tasks t
                LEFT JOIN tasks_responsible_users ru ON t.id = ru.task_id
                AND ru.user_id = $2
                WHERE t.object_id = $1
                    AND ((ru.user_id = $2) || (t.cid = $2) || (t.privacy = 0))
                ORDER BY t.cdate';
        $res = DB\dbQuery($sql, array($d->id, $_SESSION['user']['id'])) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $res2 = DB\dbQuery(
                'SELECT l'.USER_LANGUAGE_INDEX.'
                FROM users_groups
                WHERE id =$1',
                $r['cid']
            ) or die(DB\dbQueryError());

            if ($r2 = $res2->fetch_row()) {
                $r['owner'] = $r2[0];
            }
            $res2->close();

            if ($r['cid'] != $r['responsible_user_ids']) {
                $r['users'] = array();
                $res2 = DB\dbQuery(
                    'SELECT id
                         , l'.USER_LANGUAGE_INDEX.'
                    FROM users_groups
                    WHERE id IN (0'.$r['responsible_user_ids'].')
                    ORDER BY 2'
                ) or die(DB\dbQueryError());
                while ($r2 = $res2->fetch_row()) {
                    $r['users'][$r2[0]] = $r2[1];
                }
                $res2->close();
            }
            $rez['tasks'][] = $r;
        }
        $res->close();
        /* end of get Tasks */

        global $data;
        // this method is used also internally (by getInfo method),
        // so we skip logging for "load" method in this cases
        if (is_object($data) && ($data->method == 'load')) {
            Log::add(array('action_type' => 11, 'object_id' => $d->id));
        }

        return array('success' => true, 'data' => $rez);
    }
    public function create($p)
    {
        $template = $this->getTemplateInfo($p->template_id);
        if (!Security::isAdmin() && !Security::canCreateActions($p->pid)) {
            throw new \Exception(L\Access_denied);
        }
        fireEvent('beforeNodeDbCreate', $p);
        if (empty($p->name)) {
            $p->name = $template['title'];
        }
        if (empty($p->tag_id)) {
            $p->tag_id = null;
        }
        $p->name = Files::getAutoRenameFilename($p->pid, $p->name);
        $p->type = 4;
        DB\dbQuery(
            'INSERT INTO tree (pid, name, `type`, template_id, cid, tag_id, updated)
            VALUES ($1
                  , $2
                  , $3
                  , $4
                  , $5
                  , $6
                  , 1)',
            array(
                $p->pid
                ,$p->name
                ,$p->type
                ,$template['id']
                ,$_SESSION['user']['id']
                ,$p->tag_id
            )
        ) or die(DB\dbQueryError());
        $p->nid = DB\dbLastInsertId();
        DB\dbQuery(
            'INSERT INTO objects (id, `title`, template_id, cid)
            VALUES($1
                 , $2
                 , $3
                 , $4)',
            array(
                $p->nid
                ,$p->name
                ,$template['id']
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        DB\dbQuery(
            'INSERT INTO objects_data (object_id, field_id, value)
            SELECT $1
                 , id
                 , $2
            FROM templates_structure
            WHERE template_id = $3
                AND name = \'_title\'',
            array(
                $p->nid
                ,$p->name
                ,$template['id']
            )
        ) or die(DB\dbQueryError());

        $this->createSystemFolders($p->nid, @$template['cfg']['system_folders']);

        fireEvent('nodeDbCreate', $d);

        SolrClient::runCron();

        return array('success' => true, 'data' => $p);
    }

    public function save($p)
    {
        $log_action_type = 9; // update action
        $object_title = '';
        $object_custom_title = '';
        $object_date_start = null;
        $object_date_end = null;
        $object_violation = false;
        $object_author = null;
        $fields = array();
        $update_ids_icons = array();

        $d = json_decode($p['data']);
        $initial_object_id = $d->id;

        $template = $this->getTemplateInfo($d->template_id, $d->id);

        /* analisys of object id (inserting if new) */
        $isNewObject = true;
        $d->type = 4; //case object
        if (!is_numeric($d->id)) {
            // SECURITY: check if current user has access
            if (!Security::isAdmin() && !Security::canCreateActions($d->pid)) {
                throw new \Exception(L\Access_denied);
            }
            fireEvent('beforeNodeDbCreate', $d);

            DB\dbQuery(
                'INSERT INTO tree (pid, name, `type`, template_id, cid)
                VALUES ($1
                      , $2
                      , $3
                      , $4
                      , $5)',
                array(
                    $d->pid
                    ,'new case object'
                    ,4
                    ,$template['id']
                    ,$_SESSION['user']['id']
                )
            ) or die(DB\dbQueryError());
            $d->id = DB\dbLastInsertId();
            $sql = 'INSERT INTO objects (id, `title`, template_id, cid) VALUES($1, $2, $3, $4)';
            DB\dbQuery($sql, array($d->id, '', $template['id'], $_SESSION['user']['id'])) or die(DB\dbQueryError());

            $this->createSystemFolders($d->id, @$template['cfg']['system_folders']);

            $log_action_type = 8; //else throw new Eception(L\Error_creating_object); // create action
        } else {
            // SECURITY: check if current user has write access to this action
            if (!Security::isAdmin() && !Security::canWrite($d->id)) {
                throw new \Exception(L\Access_denied);
            }
            fireEvent('beforeNodeDbUpdate', $d);
            $isNewObject = false;
        }
        /* end of analizing object id */

        /* save object duplicates from grid */
        $duplicate_ids = array(0 => 0);
        if (isset($d->gridData->duplicateFields)) {
            $sql = 'INSERT INTO objects_duplicates (pid, object_id, field_id) VALUES ($1, $2, $3)';
            foreach ($d->gridData->duplicateFields as $field_id => $fv) {
                $i = 0;
                foreach ($fv as $duplicate_id => $duplicate_pid) {
                    if (!is_numeric($duplicate_id)) {
                        DB\dbQuery(
                            $sql,
                            array(
                                $duplicate_ids[$duplicate_pid]
                                ,$d->id
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
        $filter_secure_fields = Security::isAdmin() ?
            '' :
            ' AND id NOT IN
                (SELECT duplicate_id
                 FROM objects_data
                 WHERE object_id = $1
                     AND duplicate_id <> 0
                     AND private_for_user <> '.$_SESSION['user']['id'].') ';
        DB\dbQuery(
            'DELETE
            FROM objects_duplicates
            WHERE object_id = $1
                AND (id NOT IN ('.implode(', ', array_values($duplicate_ids)).'))'.$filter_secure_fields,
            $d->id
        ) or die(DB\dbQueryError());
        /* end of save object duplicates from grid */

        $object_title = str_replace(array('{template_title}', '{phase_title}'), array($template['title'], ''/*$phase['name']/**/), $template['title_template']);

        /* save object values from grid */
        $sql = 'INSERT INTO objects_data (object_id, field_id, duplicate_id, `value`, info, files, private_for_user)
                VALUES ($1
                      , $2
                      , $3
                      , $4
                      , $5
                      , $6
                      , $7) ON DUPLICATE KEY
                UPDATE object_id = $1
                    , field_id = $2
                    , duplicate_id = $3
                    , `value` = $4
                    , info = $5
                    , files = $6
                    , private_for_user = $7';
        $ids = array(0);
        $log = '';
        if (isset($d->gridData)) {
            foreach ($d->gridData->values as $f => $fv) { //$c => $cv
                if (!isset($fv->value)) {
                    $fv->value = null;
                }
                $f = explode('_', $f);
                $field_id = substr($f[0], 1);
                $field = array();
                $res = DB\dbQuery('select name, type, cfg from templates_structure where id = $1', $field_id) or die(DB\dbQueryError());
                if ($r = $res->fetch_assoc()) {
                    $field = $r;
                    if (!empty($field['cfg'])) {
                        $field['cfg'] = json_decode($field['cfg']);
                    }
                    $field['value'] = array('value' => $fv->value, 'info' => $fv->info);

                }
                $res->close();

                $duplicate_id = intval($duplicate_ids[$f[1]]);
                $duplicate_index = 0;
                if (isset($fields[$field_id]['duplicates'])) {
                    foreach ($fields[$field_id]['duplicates'] as $k => $v) {
                        if (is_array($v['id'])) {
                            if ($v['id'] == $duplicate_id) {
                                $fields[$field_id]['duplicates'][$k]['index'] = $duplicate_index;
                            } else {
                                $duplicate_index++;
                            }
                        }
                    }
                }

                $v = $fv->value;
                switch ($field['name']) {
                    case '_title':
                        $object_custom_title = $v;
                        break;
                    case '_date_start':
                        $object_date_start = $v;
                        break;
                    case '_date_end':
                        $object_date_end = $v;
                        break;
                }

                /* for titles processing */
                $v = Templates::getTemplateFieldValue($field, 'text');
                $object_title = str_replace('{f'.$field_id.'}', $v, $object_title);
                $object_title = str_replace('{'.$field['name'].'}', $v, $object_title);
                $object_title = str_replace('{'.$field['name'].'_info}', $fv->info, $object_title);
                if ($duplicate_id > 0) {
                    $fields[$field_id]['duplicates'][$duplicate_index]['value_id'] = $fv->value;
                    $fields[$field_id]['duplicates'][$duplicate_index]['value'] = $v;
                    $fields[$field_id]['duplicates'][$duplicate_index]['details'] = $fv->info;
                    $fields[$field['name']] = &$fields[$field_id];
                } else {
                    $fields[$field_id]['value_id'] = $fv->value;
                    $fields[$field_id]['value'] = $v;
                    $fields[$field_id]['details'] = $fv->info;
                    $fields[$field_id]['title'] = $field['name'];
                    $fields[$field['name']] = &$fields[$field_id];
                }
                /* end of for titles processing */
                if (empty($fv->pfu)) {
                    $fv->pfu = null;
                }
                @$params = array($d->id, $field_id, $duplicate_id, $fv->value, $fv->info, $fv->files, $fv->pfu);
                DB\dbQuery($sql, $params) or die(DB\dbQueryError());
                $res = DB\dbQuery(
                    'SELECT id
                    FROM objects_data
                    WHERE object_id = $1
                        AND field_id = $2
                        AND duplicate_id = $3',
                    array(
                        $d->id
                        ,$field_id
                        ,$duplicate_id
                    )
                ) or die(DB\dbQueryError());

                if ($r = $res->fetch_row()) {
                    array_push($ids, $r[0]);
                }
                $res->close();
            }
        }
        $filter_secure_fields = Security::isAdmin() ?
            '' : ' and ((private_for_user is null) or (private_for_user = '.$_SESSION['user']['id'].')) ';
        DB\dbQuery(
            'DELETE
            FROM objects_data
            WHERE object_id = $1
                AND (id NOT IN ('.implode(', ', $ids).'))'.$filter_secure_fields,
            $d->id
        ) or die(DB\dbQueryError());

        //replacing field titles into object title variable
        $sql = 'SELECT id
                     , name
                     , l'.USER_LANGUAGE_INDEX.'
                FROM templates_structure
                WHERE template_id = $1
                    AND (($2 LIKE concat(\'%{f\', id, \'t}%\'))
                         OR ($2 LIKE concat(\'%{\', name, \'_title}%\')))';
        $res = DB\dbQuery($sql, array($template['id'], $object_title)) or die(DB\dbQueryError());
        while ($r = $res->fetch_row()) {
            $object_title = str_replace('{f'.$r[0].'t}', $r[2], $object_title);
        }
        $res->close();
        // evaluating the title if contains php code
        if (strpos($object_title, '<?php') !== false) {
            @eval(' ?>'.$object_title.'<?php ');
            if (!empty($title)) {
                $object_title = $title;
            }
        }
        //replacing any remained field placeholder from the title
        $object_title = preg_replace('/\{[^\}]+\}/', '', $object_title);
        $object_title = stripslashes($object_title);

        // updating object properties into the db  /*(empty($object_iconCls) ? '' : ', iconCls = $7')/**/
        @DB\dbQuery(
            'UPDATE objects
            SET title = $1
                , custom_title = $2
                , date_start = $3
                , date_end = $4
                , author = $5
                , iconCls = $7
                , private_for_user = $8 '.
            ($isNewObject ? '' : ', uid = $9, udate = CURRENT_TIMESTAMP').'
            WHERE id = $6',
            array(
                ucfirst($object_title)
                ,$object_custom_title
                ,$object_date_start
                ,$object_date_end
                ,$object_author
                ,$d->id
                ,$this->getObjectIcon($d->id)
                ,$d->pfu
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());
        /* end of updating object properties into the db */

        Objects::updateCaseUpdateInfo($d->id);

        Log::add(array('action_type' => $log_action_type, 'object_id' => $d->id));

        $update_ids_icons = array_keys($update_ids_icons);
        foreach ($update_ids_icons as $id) {
            DB\dbQuery(
                'UPDATE objects
                SET iconCls = $1
                WHERE id = $2',
                array(
                    $this->getObjectIcon($id)
                    ,$id
                )
            ) or die(DB\dbQueryError());
        }

        if ($isNewObject) {
            fireEvent('nodeDbCreate', $d);
        } else {
            fireEvent('nodeDbUpdate', $d);
        }

        SolrClient::runCron();

        $p = (object) array( 'data' => (object) array( "id" => $d->id, "template_id" => $template['id'] ) );

        return $this->load($p);
    }

    public function queryCaseData($queries)
    {
        $rez = array('success' => true);
        foreach ($queries as $key => $query) {
            $query->pids = $query->caseId;
            switch ($key) {
                case 'properties':
                    /* load general case properties */
                    $rez[$key] = $this->load((object) array( 'data' => (object) array( 'id' => $query->caseId) ));
                    // $r = $this->getCasePropertiesObjectId($query->caseId);
                    if (!empty($query->caseId)) {
                        $template_id = null;
                        $properties = array();
                        $sql = 'select template_id from tree where id = $1';
                        $res = DB\dbQuery($sql, $query->caseId) or die(DB\dbQueryError());
                        if ($r = $res->fetch_assoc()) {
                            $template_id = $r['template_id'];
                        }
                        $res->close();

                        $tf = Templates::getTemplateFieldsWithData($template_id, $query->caseId);
                        if (!empty($tf)) {
                            foreach ($tf as $f) {
                                if ($f['name'] == '_title') {
                                    continue;
                                }
                                if ($f['name'] == '_date_start') {
                                    continue;
                                }
                                $v = Templates::getTemplateFieldValue($f);
                                if (is_array($v)) {
                                    $v = implode(', ', $v);
                                }
                                $f['value'] = $v;
                                $properties[] = array(
                                    'name' => $f['name']
                                    ,'title' => $f['title']
                                    ,'type' => $f['type']
                                    ,'cfg' => $f['cfg']
                                    ,'value' => $v
                                );
                            }
                        }
                        $rez[$key]['data']['properties'] = $properties;
                    }

                    break;
                case 'actions':
                    $s= new Search();
                    $query->fl = 'id,pid,name,type,subtype,date,template_id,cid';
                    $query->template_types = 'object';
                    $query->sort = array('date desc');
                    $rez[$key] = $s->query($query);
                    unset($s);
                    break;
                case 'tasks':
                    $s= new Search();
                    $query->fl = 'id,name,type,template_id,date,date_end,cid,user_ids';
                    $query->template_types = 'task';
                    $query->sort = array('date desc');
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
    // This function is supposed to be used from communications glid list, to get the objects short info
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
            $data = $this->load(json_decode('{"data":{"id":'.$id.'} }'));
        } catch (\Exception $e) {
            return '';
        }
        $data = $data['data'];

        $gf = Templates::getGroupedTemplateFieldsWithData($data['template_id'], $id);

        if (!empty($gf['top'])) {
            foreach ($gf['top'] as $f) {
                if ($f['name'] == '_title') {
                    continue;
                }
                if ($f['name'] == '_date_start') {
                    continue;
                }
                $v = Templates::getTemplateFieldValue($f);
                if (is_array($v)) {
                    $v = implode(', ', $v);
                }
                if (!empty($v)) {
                    $top .= '<tr><td class="prop-key">'.$f['title'].'</td><td class="prop-val">'.$v.'</td></tr>';
                }
            }
        }
        if (!empty($gf['body'])) {
            foreach ($gf['body'] as $f) {
                $v = Templates::getTemplateFieldValue($f);
                if (is_array($v)) {
                    $v = implode('<br />', $v);
                }

                if (empty($v) && empty($f['value']['info']) && empty($f['value']['files'])) {
                    continue;
                }
                $body .= '<tr><td'.(empty($f['level']) ? '' : ' style="padding-left: '.($f['level'] * 10).'px"').
                    ' class="prop-key">'.$f['title'].'</td><td class="prop-val">'.$v.
                    (empty($f['value']['info']) ? '' : '<p class="prop-info">'.$f['value']['info'].'</p>').'</td></tr>';
            }
        }

        $tmp = Files::getFilesBlockForPreview($id);
        if (!empty($tmp)) {
            $bottom .= '<div class="obj-preview-h pt10">'.L\Files.'</div>'.$tmp.'<br />';
        }
        $tmp = Tasks::getAxtiveTasksBlockForPreview($id);
        if (!empty($tmp)) {
            $bottom .= '<div class="obj-preview-h pt10">'.L\ActiveTasks.'</div>'.$tmp.'<br />';
        }

        if (!empty($gf['bottom'])) {
            foreach ($gf['bottom'] as $f) {
                $v = Templates::getTemplateFieldValue($f);
                if (empty($v)) {
                    continue;
                }
                $bottom .=  '<div class="obj-preview-h">'.$f['title'].'</div>'.$v.'<br />';
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
                if (!defined('CB\\CONFIG\\RESPONSIBLE_PARTY_DEFAULT') || (CONFIG\RESPONSIBLE_PARTY_DEFAULT != $t['responsible_party_id'])) {
                    /* append responsible part */
                    $small_fields[] = L\Party.': '.$t['responsible_party'];
                }
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
                '<tr class="bgcLG cG"><th width="20%" class="icon-padding icon-calendar-task">'.L\Tasks.'</th><th width="25%">'.L\Created.'</th><th width="30%">'.L\Deadline.'</th></tr><tr>'.implode('</tr><tr>', $d).'</tr></table>';
            }
        }
        /* end of tasks */

        Log::add(array('action_type' => 12, 'object_id' => $data['id'] ));
        if (!empty($top)) {
            $top = '<div class="obj-preview-h">'.L\Details.'</div>'.$top;
        }
        $top .= $body;
        if (!empty($top)) {
            $top = '<table class="obj-preview">'.$top.'</table><br />';
        }

        return '<div style="padding:10px">'.$top.$bottom.'</div>';
    }

    public static function getAssociatedObjects($p)
    {
        $data = array();
        if (is_numeric($p)) {
            $p = (object) array('id' => $p);
        }
        if (empty($p->id) && empty($p->template_id)) {
            return array('success' => true, 'data' => $data, 's'=>'1');
        }

        $ids = array();

        if (!empty($p->id)) {
            // SECURITY: check if current user has at least read access to this case
            if (!Security::isAdmin() && !Security::canRead($p->id)) {
                throw new \Exception(L\Access_denied);
            }

            /* select distinct associated case ids from the case */
            $sql = 'SELECT DISTINCT d.value
                    FROM tree o
                    JOIN templates_structure s ON o.template_id = s.template_id AND s.type = \'_objects\'
                    JOIN objects_data d on. d.field_id = s.id
                    WHERE o.id = $1';
            $res = DB\dbQuery($sql, $p->id) or die(DB\dbQueryError());
            while ($r = $res->fetch_row()) {
                $a = Util\toNumericArray($r[0]);
                foreach ($a as $id) {
                    $ids[$id] = 1;
                }
            }
            $res->close();
        }
        if (!empty($p->template_id)) {
            $sql = 'SELECT DISTINCT cfg
                    FROM templates_structure
                    WHERE template_id = $1
                        AND (cfg IS NOT NULL)';
            $res = DB\dbQuery($sql, $p->template_id) or die(DB\dbQueryError());
            while ($r = $res->fetch_row()) {
                if (empty($r[0])) {
                    continue;
                }
                $cfg = json_decode($r[0]);
                if (!empty($cfg->value)) {
                    $a = Util\toNumericArray($cfg->value);
                    foreach ($a as $id) {
                        $ids[$id] = 1;
                    }
                }
            }
            $res->close();
        }

        $ids = array_keys($ids);
        if (empty($ids)) {
            return array('success' => true, 'data' => array());
        }
        /* end of select distinct case ids from the case */
        $sql = 'SELECT DISTINCT t.id
                  , t.`name`
                  , t.date
                  , t.`type`
                  , t.subtype
                  , t.template_id
                  , t2.status
                FROM tree t
                LEFT JOIN tasks t2 ON t.id = t2.id
                WHERE t.id IN ('.implode(', ', $ids).')
                ORDER BY 2';
        $res = DB\dbQuery($sql) or die(DB\dbQueryError());
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

    private function getTemplateInfo($template_id = false, $object_id = false)
    {
        $rez = array();
        if (is_numeric($template_id)) {
            $res = DB\dbQuery(
                'SELECT id
                    ,pid
                    ,`type`
                    ,l'.USER_LANGUAGE_INDEX.' `title`
                     ,iconCls
                     ,default_field
                     ,title_template
                     ,cfg
                FROM templates
                WHERE id = $1',
                $template_id
            ) or die(DB\dbQueryError());

            if ($r = $res->fetch_assoc()) {
                $rez = $r;
            }
            $res->close();
        } elseif (is_numeric($object_id)) {
            $res = DB\dbQuery('SELECT id, pid, type, t.l'.USER_LANGUAGE_INDEX.' `title`, iconCls, default_field, title_template, cfg from templates t  where id = (select template_id from tree where id = $1)', $object_id) or die(DB\dbQueryError());
            if ($r = $res->fetch_assoc()) {
                $rez = $r;
            }
            $res->close();
        }
        if (!empty($rez['cfg'])) {
            $rez['cfg'] = json_decode($rez['cfg'], true);
        }

        return $rez;
    }

    private function createSystemFolders($object_id, $folderIds)
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
            $sql = 'SELECT l'.USER_LANGUAGE_INDEX.', `type` FROM tags WHERE id = $1';
            $res = DB\dbQuery($sql, $node['tag_id']) or die( DB\dbQueryError() );
            if ($r = $res->fetch_row()) {
                $node['name'] = $r[0];
                $node['type'] = $r[1];
                $node['template_id'] = CONFIG\DEFAULT_FOLDER_TEMPLATE;
            }
            $res->close();
            /* end of get tag name & type*/
            if ($node['type'] == 1) {
                $rez = $this->create((Object)$node);
                $pid = $rez['data']->nid;
            } else {
                $pid = $node['pid'];
            }

            /* checking if childs exist for added folder and append them to childs table */
            $sql = 'SELECT id FROM tags WHERE pid = $1';
            $res = DB\dbQuery($sql, $node['tag_id']) or die( DB\dbQueryError() );
            while ($r = $res->fetch_row()) {
                $childs[] = array('pid' => $pid, 'tag_id' => $r[0]);
            }
            $res->close();
            /* end of checking if childs exist for added folder and append them to childs table */
        }
    }

    private function getObjectIcon($object_id)
    {
        $rez = null;

        /* -- default icon by template /**/
        $res = DB\dbQuery(
            'SELECT t.iconCls
            FROM tree o
            JOIN templates t ON o.template_id = t.id
            WHERE o.id = $1',
            $object_id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_row()) {
            $rez = $r[0];
        }
        $res->close();

        return $rez;
    }

    public static function getFieldValue($object_id, $field_id, $duplicate_id = 0)
    {
        $rez = null;
        $sql = 'SELECT value
                FROM objects_data
                WHERE object_id = $1
                    AND field_id = $2
                    AND duplicate_id = $3';
        $res = DB\dbQuery($sql, array($object_id, $field_id, $duplicate_id)) or die(DB\dbQueryError());
        if ($r = $res->fetch_row()) {
            $rez = $r[0];
        }
        $res->close();

        return $rez;
    }

    public static function setFieldValue($object_id, $field_id, $value, $duplicate_id = 0)
    {
        $rez = null;
        $sql = 'INSERT INTO objects_data (object_id, field_id, duplicate_id, `value`)
                VALUES($1
                     , $2
                     , $3
                     , $4) ON duplicate KEY
                UPDATE `value` = $4';
        DB\dbQuery($sql, array($object_id, $field_id, $duplicate_id, $value)) or die(DB\dbQueryError());
    }

    public static function getSolrData($id)
    {
        $rez = array();
        $lang_field = 'l'.LANGUAGE_INDEX;
        $sql = 'SELECT
            co.id
            ,co.template_id
            ,co.cid
            ,co.name `title`
            ,t.iconCls
            FROM tree co left join templates t on co.template_id = t.id where co.id = $1';
            //,co.private_for_user

        $res = DB\dbQuery($sql, $id) or die(DB\dbQueryError()."\n".$sql);
        while ($r = $res->fetch_assoc()) {
            $rez['template_id'] = $r['template_id'];
            $rez['content'] = '';//$r['title']."\n";
            $rez['iconCls'] = $r['iconCls'];

            $sql = 'SELECT ts.name
                    ,ts.'.$lang_field.' `title`
                    ,ts.`type`
                    ,ts.cfg
                    ,ts.solr_column_name
                    ,d.`value`
                    ,info, files '.
                'FROM objects o '.
                'JOIN objects_data d ON d.object_id = o.id '.
                'JOIN templates_structure ts ON ts.template_id = o.template_id AND ts.id = d.field_id '.
                'WHERE o.id = $1 and (d.private_for_user is null)';//and ts.solr_column_name IS NOT NULL
            $dres = DB\dbQuery($sql, $id) or die(DB\dbQueryError()."\n".$sql);
            while ($dr = $dres->fetch_assoc()) {

                $processed_values = array();
                if (!empty($dr['value'])) {
                    /* make changes to value if needed */
                    switch ($dr['type']) {
                        case 'boolean':
                        case 'checkbox':
                        case 'object_violation':
                            $dr['value'] = empty($dr['value']) ? false : true;
                            break;
                        case 'date':
                            $dr['value'] .= 'Z';
                            if (@$dr['value'][10] == ' ') {
                                $dr['value'][10] = 'T';
                            }
                            break;
                        //case 'object_author':
                        case 'combo':
                        case 'popuplist':
                            $dr['value'] = Util\toNumericArray($dr['value']);
                            if (empty($dr['value'])) {
                                break;
                            }
                            $sql = 'select '.$lang_field.' from tags where id in ('.implode(',', $dr['value']).')';
                            $sres = DB\dbQuery($sql) or die(DB\dbQueryError()."\n".$sql);
                            while ($sr = $sres->fetch_row()) {
                                $processed_values[] = $sr[0];
                            }
                            $sres->close();
                            break;
                        case 'html':
                            $dr['value'] = strip_tags($dr['value']);
                            //$processed_values[] = strip_tags($dr['value']);
                            break;
                        case '_auto_title':
                        case 'memo':
                        case 'text':
                        case 'int':
                        case 'float':
                        case 'time':
                        default:
                            break;
                    }
                    /* make changes to value if needed */

                    $field_config = json_decode($dr['cfg'], true);
                    if (@$field_config['faceting']) {
                        $solr_field = $dr['solr_column_name'];
                        if (empty($solr_field)) {
                            $solr_field = ( empty($field_config['source']) || ($field_config['source'] == 'thesauri') ) ?
                                'sys_tags' : 'tree_tags';
                        }
                        $arr = Util\toNumericArray($dr['value']);
                        for ($i=0; $i < sizeof($arr); $i++) {
                            //$rez[$solr_field][$arr[$i]] = 1;
                            if (empty($rez[$solr_field]) || !in_array($arr[$i], $rez[$solr_field])) {
                                $rez[$solr_field][] = $arr[$i];
                            }
                        }
                    }
                }

                if (!empty($dr['value'])) {
                    if (!empty($processed_values)) {
                        foreach ($processed_values as $v) {
                            $rez['content'] .= $dr['title'].' '.$v."\n";
                        }
                    } elseif (!empty($dr['value'])) {
                        if (!is_array($dr['value'])) {// $dr['value'] = implode(' ', $dr['value']);
                            $rez['content'] .= $dr['title'].' '.
                                (in_array($dr['solr_column_name'], array('date_start', 'date_end', 'dates')) ?
                                    substr($dr['value'], 0, 10): $dr['value'])."\n";
                        }
                    }
                }
            }
            $dres->close();
        }
        $res->close();

        // if(!empty($rez['sys_tags'])) $rez['sys_tags'] = array_keys($rez['sys_tags']);
        // else unset($rez['sys_tags']);

        // if(!empty($rez['tree_tags'])) $rez['tree_tags'] = array_keys($rez['tree_tags']);
        // else unset($rez['tree_tags']);
        return $rez;
    }

    public static function getCaseId($node_id)
    {
        $case_id = null;
        $sql = 'select case_id from tree_info where id = $1';
        $res = DB\dbQuery($sql, $node_id) or die(DB\dbQueryError());
        if ($r = $res->fetch_row()) {
            $case_id = $r[0];
        }
        $res->close();

        return $case_id;
    }

    public static function getCaseName($case_id = false)
    {
        /*function deemed to get case name by its Id*/
        $rez = false;
        $res = DB\dbQuery(
            'SELECT name FROM tree WHERE id = $1',
            $case_id
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_row()) {
            $rez = $r[0];
        }
        $res->close();

        return $rez;
    }

    public static function updateCaseUpdateInfo($case_or_caseObject_id)
    {
        // DB\dbQuery('update tree set uid = $2, udate = CURRENT_TIMESTAMP
        // where id = `f_get_objects_case_id`($1)',
        // array($case_or_caseObject_id, $_SESSION['user']['id'] )) or die(DB\dbQueryError());
    }

    /* setting case roles fields for an object data */
    public static function setCaseRolesFields(&$objectData)
    {
        $case_id = null;//237
        $db = null;

        $sql = 'select DATABASE(), case_id from tree where id = $1';
        $res = DB\dbQuery($sql, $objectData['id']) or die(DB\dbQueryError());
        if ($r = $res->fetch_row()) {
            $db = $r[0];
            $case_id = $r[1];
        }
        $res->close();

        if (empty($case_id)) {
            return;
        }

        // check if cached
        if (isset($GLOBALS[$db][$case_id])) {
            foreach ($GLOBALS[$db][$case_id] as $k => $v) {
                $objectData[$k] = $v;
            }

            return;
        }

        $GLOBALS[$db][$case_id] = array();
        $sql = 'SELECT solr_column_name, od.value FROM objects_data od '.
            'JOIN templates_structure t ON od.`field_id` = t.`id` AND solr_column_name LIKE \'role_ids%\' '.
            'WHERE object_id = $1';
        $res = DB\dbQuery($sql, $case_id) or die(DB\dbQueryError());

        while ($r = $res->fetch_row()) {
            if (!empty($r[1])) {
                $GLOBALS[$db][$case_id][$r['0']] = explode(',', $r[1]);
                $objectData[$r['0']] = explode(',', $r[1]);
            }
        }
        $res->close();
    }
}
