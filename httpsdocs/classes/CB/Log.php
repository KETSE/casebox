<?php
namespace CB;

class Log
{
    public function getLastLog()
    {
        $data = array();
        $res = DB\dbQuery(
            'SELECT l'.USER_LANGUAGE_INDEX.' html, date
            FROM actions_log
            WHERE pid IS NULL
            ORDER BY `date` DESC, id DESC LIMIT 50'
        ) or die(DB\dbQueryError());
        while ($r = $res->fetch_row()) {
            $data[] = array($r[0].' '.Util\formatPastTime($r[1]));
        }

        return array(
            'success' => true
            ,'data' => $data
        );
    }

    public static function add($p)
    {
        // Available table fields: id, user_id, to_user_ids, case_id, object_id, file_id, office_index, action_type, result, info
        // id can be used to update an existing row
        $id = null;
        $case = array();
        @$obj = array(
            'id' => $p['object_id']
            ,'title' => ''
            ,'iconCls' => ''
        );
        $task = array();
        $to_user_ids = array();
        if (!is_array($p)) {
            return false;// if no params specified then exit
        }
        if (isset($_SESSION['user']['id'])) {
            $p['user_id'] = $_SESSION['user']['id'];
        }

        //setting case_id if not specified and we have object_id or file_id specified
        if (empty($p['case_id']) && (!empty($p['object_id']) || !empty($p['file_id']) || !empty($p['task_id']))) {
            try {
                @$p['case_id'] = Objects::getCaseId(Util\coalesce($p['object_id'], $p['file_id'], $p['task_id']));
            } catch (\Exception $e) {
                //Task is independent, not associated
            }
        }
        // get case data
        if (!empty($p['case_id'])) {
            $res = DB\dbQuery(
                'SELECT id
                     , name
                FROM tree
                WHERE id = $1',
                $p['case_id']
            ) or die(DB\dbQueryError());

            if ($r = $res->fetch_assoc()) {
                $case_data = $r;
            }
            $res->close();
        } else {
            $p['case_id'] = null;
        }
        // get object data
        if (!empty($p['object_id'])) {
            $sql = 'SELECT o.id
                     , coalesce(o.custom_title, o.title) `title`
                     , t.iconCls
                FROM objects o
                JOIN templates t ON o.template_id = t.id
                WHERE o.id = $1';
            $res = DB\dbQuery($sql, $p['object_id']) or die(DB\dbQueryError());
            if ($r = $res->fetch_assoc()) {
                $obj = $r;
            }
            $res->close();
        } else {
            $p['object_id'] = null;
        }
        // get task data
        if (!empty($p['task_id'])) {
            $res = DB\dbQuery('select title from tasks where id = $1', $p['task_id']) or die(DB\dbQueryError());
            if ($r = $res->fetch_assoc()) {
                $task = $r;
            }
            $res->close();
        } else {
            $p['task_id'] = null;
        }
        // get file data
        $file = array();
        if (!empty($p['file_id'])) {
            $res = DB\dbQuery('select id, name from files where id = $1', $p['file_id']) or die(DB\dbQueryError());
            if ($r = $res->fetch_assoc()) {
                $file = $r;
                $t = explode('.', $file['name']);
                $file['a'] = ' <i class="file-unknown file-'.array_pop($t).'" id="'.$file['id'].'" id2 ="'.$p['case_id'].'">'.$file['name'].'</i>';
            }
            $res->close();
        } else {
            $p['file_id'] = null;
        }
        // get destination usernames if "to_user_ids" is present in params
        $to_user_names_data = array();
        if (!empty($p['to_user_ids'])) {
            if (!is_array($p['to_user_ids'])) {
                $to_user_ids = explode(',', $p['to_user_ids']);
            }
            $to_user_ids = array_filter($to_user_ids, 'is_numeric');
        }
        if (!empty($to_user_ids)) {
            $p['to_user_ids'] = implode(',', $to_user_ids);
            $res = DB\dbQuery('select name, '.CONFIG\LANGUAGE_FIELDS.', sex from users_groups where id in ('.$p['to_user_ids'].')') or die(DB\dbQueryError());
            while ($r = $res->fetch_assoc()) {
                $to_user_names_data[] = $r;
            }
            $res->close();
        }

        $u = &$_SESSION['user'];
        //create the htmls for each language
        // $template_types_translation_names = array('', 'Object', 'IncomingAction', 'OutgoingAction', 'User', 'Contact', 'Organization' );

        require_once DOC_ROOT.'language.php';
        L\initTranslations();
        $fields = array('id', 'pid', 'user_id', 'to_user_ids', 'case_id', 'object_id', 'file_id', 'task_id', 'date', 'action_type', 'remind_users', 'result', 'info');
        if (!empty($GLOBALS['languages'])) {
            for ($lk=0; $lk < sizeof($GLOBALS['languages']); $lk++) {
                $l = 'l'.($lk+1);
                $fields[] = $l;

                @$case['a'] = ' <i class="case" id="'.$p['case_id'].'">'.(!empty($case_data['name']) ? $case_data['name'] : ((!empty($case_data['nr'])) ? L\get('Nr', $l).' '.$case_data['nr'] : 'id: '.$case_data['id']) ).'</i>';

                @$obj['a'] = ' <i class="obj'.(empty($obj['iconCls']) ? '' : ' '.$obj['iconCls']).'" id="'.$obj['id'].'">'.$obj['title'].'</i>';
                @$obj['type'] = '';//L\get($template_types_translation_names[$obj['type_id']], $l);

                @$task['a'] = ' "<i class="task">'.$task['title'].'</i>"';

                $username = empty($u[$l]) ? @$u['name'] : $u[$l];
                $to_user_names = array();
                if (!empty($to_user_names_data)) {
                    foreach ($to_user_names_data as $tu) {
                        $to_user_names[] = '<i class="icon-user-'.$tu['sex'].'">'.(empty($tu[$l]) ? $tu['name'] : $tu[$l]).'</i>';
                    }
                    $to_user_names = implode(', ', $to_user_names);
                }/**/

                @$sex = $u['sex'];
                $p[$l] = '<i class="icon-user-'.$sex.'">'.$username.'</i> ';
                switch ($p['action_type']) { //- log actions
                    case 1: //Login
                        $p[$l] .= Log::getGenderString($sex, 'LoggedOn', $l);
                        break;
                    case 2: // Logout
                        $p[$l] .= Log::getGenderString($sex, 'LoggedOut', $l).' '.L\get('fromTheSystem', $l);
                        break;
                    case 3: // Add case
                        $p[$l] .= Log::getGenderString($sex, 'Added', $l).' '.L\get('theCase', $l).$case['a']; //(($sex == 'f') ? 'добавила' : 'добавил').' дело '.$case['a'];
                        break;
                    case 4: // update case
                        $p[$l] .= Log::getGenderString($sex, 'Updated', $l).' '.L\get('theCase', $l).$case['a'];
                        break;
                    case 5: // delete case
                        $p[$l] .= Log::getGenderString($sex, 'Deleted', $l).' '.L\get('case', $l).$case['a'];
                        break;
                    case 6: // open case
                        $p[$l] .= Log::getGenderString($sex, 'Opened', $l).' '.L\get('theCase', $l).$case['a'];
                        break;
                    case 7: // close case
                        $p[$l] .= Log::getGenderString($sex, 'Closed', $l).' '.L\get('theCase', $l).$case['a'];
                        break;
                    case 8: // add case object
                        $p[$l] .= Log::getGenderString($sex, 'Added', $l).' '.$obj['type'].$obj['a'].' '.L\get('toCase', $l).$case['a'];
                        break;
                    case 9: // update case object
                        $p[$l] .= Log::getGenderString($sex, 'Updated', $l).' '.$obj['type'].$obj['a'].' '.L\get('inCase', $l).$case['a'];
                        break;
                    case 10: // delete case object
                        $p[$l] .= Log::getGenderString($sex, 'Deleted', $l).' '.$obj['type'].$obj['a'].' '.L\get('fromCase', $l).$case['a'];
                        break;
                    case 11: // open case object
                        $p[$l] .= Log::getGenderString($sex, 'Opened', $l).' '.$obj['type'].$obj['a'].' '.L\get('ofCase', $l).$case['a'];
                        break;
                    case 12: // get case objects info
                        $p[$l] .= Log::getGenderString($sex, 'Viewed', $l).' '.$obj['type'].$obj['a'].' '.L\get('ofCase', $l).$case['a'];
                        break;
                    case 13: // add case file
                        $p[$l] .= Log::getGenderString($sex, 'Added', $l).' '.L\get('file', $l).$file['a'].' ';
                        if (empty($p['object_id'])) {
                            $p[$l] .= L\get('toCase', $l).$case['a'];
                        } else {
                            $p[$l] .= L\get('ofCase', $l).$obj['a'].' '.L\get('ofCase', $l).$case['a'];
                        }
                        break;
                    case 14: // download case file
                        $p[$l] .= Log::getGenderString($sex, 'Downloaded', $l).' '.L\get('theFile', $l).$file['a'].' '.L\get('ofCase', $l).$case['a'];
                        break;
                    case 15: // delete case file
                        $p[$l] .= Log::getGenderString($sex, 'Deleted', $l).' '.L\get('file', $l).$file['a'].' '.L\get('fromCase', $l).$case['a'];
                        break;
                    case 16: // update case access
                        $p[$l] .= Log::getGenderString($sex, 'Updated', $l).' '.L\get('securityRights', $l).' '.L\get('forCase', $l).$case['a'];
                            //(empty($p['to_user_id']) ? '' : ', адвокату <a class="icon-user-lawyer" href="">'.$to_user_name.'</a>');/**/
                        break;
                        /*case 17: // add access to case
                        $p[$l] .= Log::getGenderString($sex, 'Added', $l).(($sex == 'f') ? 'передала' : 'передал').' дело "'.$case['a'].'" в оффис "'.$office_name.'"'.
                                (empty($p['to_user_id']) ? '' : ', адвокату <a class="icon-user-lawyer" href="">'.$to_user_name.'</a>');/**/
                        /*  break;
                        case 18: // remove access from case
                        $p[$l] .= Log::getGenderString($sex, 'Added', $l).(($sex == 'f') ? 'передала' : 'передал').' дело "'.$case['a'].'" в оффис "'.$office_name.'"'.
                                (empty($p['to_user_id']) ? '' : ', адвокату <a class="icon-user-lawyer" href="">'.$to_user_name.'</a>');/**/
                        /*  break;
                        case 19: // grant access to case
                            $p[$l] .= Log::getGenderString($sex, 'Added', $l).(($sex == 'f') ? 'открыла' : 'открыл').' доступ пользователю <a class="icon-user" href="">'.$to_user_name.'</a> к делу '.$case['a'];
                            break;
                        case 20: // close access to case
                            $p[$l] .= Log::getGenderString($sex, 'Added', $l).(($sex == 'f') ? 'закрыла' : 'закрыл').' доступ пользователю <a class="icon-user" href="">'.$to_user_name.'</a> к делу '.$case['a'];
                            break;/* */
                    case 21: // add task
                        $p[$l] .= Log::getGenderString($sex, 'Added', $l).' '.L\get('task', $l).$task['a'].' '.( $obj['id'] ? L\get('for', $l).' '.$obj['type'].$obj['a'] : '').( empty($p['case_id']) ? '' :' '.L\get('ofCase', $l).$case['a']);
                        if (!empty($to_user_ids) && ($p['to_user_ids'] != $p['user_id'])) {
                            $p[$l] .= ' '.( (sizeof($to_user_ids) > 1) ? L\get('forUsers', $l) : L\get('forUser', $l) ).' '.$to_user_names;
                        }
                        break;
                    case 22: // update task
                        $p[$l] .= Log::getGenderString($sex, 'Updated', $l).' '.L\get('theTask', $l).' '.$task['a'].' '.( $obj['id'] ? L\get('for', $l).' '.$obj['type'].$obj['a'] : '').( empty($p['case_id']) ? '' : ' '.L\get('ofCase', $l).$case['a']);
                        if (!empty($to_user_ids) && ($p['to_user_ids'] != $p['user_id'])) {
                            $p[$l] .= ' '.( (sizeof($to_user_ids) > 1) ? L\get('forUsers', $l) : L\get('forUser', $l) ).' '.$to_user_names;
                        }
                        break;
                    case 23: // complete task by a user
                        $p[$l] .= Log::getGenderString($sex, 'Completed', $l).' '.L\get('theTask', $l).$task['a'].' '.
                        ( $obj['id'] ? L\get('for', $l).' '.$obj['type'].$obj['a'] : '').( empty($p['case_id']) ? '' : ' '.L\get('ofCase', $l).$case['a']).
                        ($p['autoclosed'] ? ' '.L\get('and', $l).' '.L\get('theTask', $l).' '.L\get('hasBeenAutoclosed', $l): '');
                        //if(!empty($p['to_user_id']) && ($p['to_user_id'] != $p['user_id'])) $p[$l] .= ' пользователя <a class="icon-user" href="">'.$to_user_name.'</a>';
                        break;
                    case 24: // remove task
                        $p[$l] .= Log::getGenderString($sex, 'Deleted', $l).' '.L\get('theTask', $l).$task['a'].' '.( $obj['id'] ? L\get('from', $l).' '.$obj['type'].$obj['a'] : '').( empty($p['case_id']) ? '' : ' '.L\get('ofCase', $l).$case['a']);
                        //if(!empty($p['to_user_id']) && ($p['to_user_id'] != $p['user_id'])) $p[$l] .= ' пользователя <a class="icon-user" href="">'.$to_user_name.'</a>';
                        break;
                    case 25: // update notifications
                        $p[$l] .= Log::getGenderString($sex, 'Updated', $l).' '.L\get('remindersForTask', $l).$task['a'].' '.( $obj['id'] ? L\get('from', $l).' '.$obj['type'].$obj['a'] : '').( empty($p['case_id']) ? '' : ' '.L\get('ofCase', $l).$case['a']);
                        //if(!empty($p['to_user_id']) && ($p['to_user_id'] != $p['user_id'])) $p[$l] .= ' пользователя <a class="icon-user" href="">'.$to_user_name.'</a>';
                        break;
                    case 26: // change user status for task
                        $p[$l] .= Log::getGenderString($sex, 'Changed', $l).' '.L\get('theStatus', $l).' '.L\get('forUser', $l).' '.$to_user_names.' '.L\get('in', $l).' '.L\get('theTask', $l).$task['a'].' '.( $obj['id'] ? L\get('for', $l).' '.$obj['type'].$obj['a'] : '').( empty($p['case_id']) ? '' : ' '.L\get('ofCase', $l).$case['a']).
                        ($p['autoclosed'] ? ' '.L\get('and', $l).' '.L\get('theTask', $l).' '.L\get('hasBeenAutoclosed', $l): '');
                        //if(!empty($p['to_user_id']) && ($p['to_user_id'] != $p['user_id'])) $p[$l] .= ' пользователя <a class="icon-user" href="">'.$to_user_name.'</a>';
                        break;
                    case 27: // close task
                        $p[$l] .= Log::getGenderString($sex, 'Closed', $l).' '.L\get('theTask', $l).$task['a'].' '.( $obj['id'] ? L\get('for', $l).' '.$obj['type'].$obj['a'] : '').( empty($p['case_id']) ? '' : ' '.L\get('ofCase', $l).$case['a']);
                        //if(!empty($p['to_user_id']) && ($p['to_user_id'] != $p['user_id'])) $p[$l] .= ' пользователя <a class="icon-user" href="">'.$to_user_name.'</a>';
                        break;
                    /*case 28: // autoclose task
                        $p[$l] .= Log::getGenderString($sex, 'Closed', $l).' '.L\get('task', $l).$task['a'].' '.( $obj['id'] ? L\get('for', $l).' '.$obj['type'].$obj['a'] : '').( empty($p['case_id']) ? '' : ' '.L\get('ofCase', $l).$case['a']);
                        //if(!empty($p['to_user_id']) && ($p['to_user_id'] != $p['user_id'])) $p[$l] .= ' пользователя <a class="icon-user" href="">'.$to_user_name.'</a>';
                        break;/**/
                }
            }
        }
        /* setting remind_users field /**/
        if (isset($p['remind_users'])) {
            $p['remind_users'] = Util\toNumericArray($p['remind_users']);
            // $p['remind_users'] = array_diff($p['remind_users'], array($_SESSION['user']['id'])); //do not remind the user that have made changes
            if (empty($p['remind_users'])) {
                unset($p['remind_users']);
            } else {
                $p['remind_users'] = implode(',', $p['remind_users']);
            }
        }/**/
        $i = 1;
        $fn = array();
        $fv = array();
        $ufv = array();
        $values = array();
        foreach ($p as $k => $v) {
            if (in_array($k, $fields)) {
                $fn[] = $k;
                $fv[] = '$'.$i;
                $ufv[] = $k.' = $'.$i;
                $values[] = $p[$k];
                $i++;
            }
        }

        $sql = 'INSERT INTO actions_log ('.implode(',', $fn).') VALUES ('.implode(',', $fv).
            ') on duplicate key UPDATE '.implode(',', $ufv);
        DB\dbQuery($sql, $values) or die(DB\dbQueryError());
        if (!empty($p['remind_users'])) {
            Log::addNotifications($p);
        }

        return DB\dbLastInsertId();
    }

    private static function getGenderString($sex, $property, $language = false)
    {
        /* this function return translation for specified property with prefixes "he" or "she" from global translation variable L */
        $prefix = ($sex == 'f') ? 'she' : 'he';
        $property = $prefix.ucfirst($property);

        return L\get($property, $language);
    }

    private static function addNotifications(&$p)
    {
        /*$p:;
        array(12) {
        ["action_type"]=> 21
        ["case_id"]=>2
        ["object_id"]=>NULL
        ["task_id"]=>52
        ["to_user_ids"]=>'1,4'
        ["remind_users"]=>'4'
        ["removed_users"]=>'4'
        ["info"]=>'title: test3'
        ["user_id"]=>'1'
        ["file_id"]=>NULL
        ["l1"]=>'<i class=\"icon-user-m\">Vitalie Ţurcanu</i> added task \"<i class=\"task\">test3</i>\"  to case <i class=\"case\" id=\"2\">A test case</i> for users <i class=\"icon-user-m\">Vitalie Ţurcanu</i>, <i class=\"icon-user-m\">Dmitry Kazakov</i>'
        ["l2"]=>'<i class=\"icon-user-m\">Vitalie Ţurcanu</i> a ajouté tâche \"<i class=\"task\">test3</i>\"  en cas <i class=\"case\" id=\"2\">A test case</i> pour les utilisateurs <i class=\"icon-user-m\">Vitalie Ţurcanu</i>, <i class=\"icon-user-m\">Dmitry Kazakov</i>'
        ["l3"]=>'<i class=\"icon-user-m\">Виталий Цуркану</i> добавил задание \"<i class=\"task\">test3</i>\"  к делу <i class=\"case\" id=\"2\">A test case</i> для пользователей <i class=\"icon-user-m\">Виталий Цуркану</i>, <i class=\"icon-user-m\">Дмитрий Казаков</i>'
        }
        */
        $to_user_ids = array();
        if (!empty($p['remind_users'])) {
            $to_user_ids = Util\toNumericArray($p['remind_users']);
        }
        if (empty($to_user_ids)) {
            return ;
        }

        $users_data = array();
        $res = DB\dbQuery(
            'SELECT id
                 , language_id
            FROM users_groups
            WHERE id IN ('.implode(', ', $to_user_ids).')'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $users_data[] = $r;
        }
        $res->close();
        foreach ($users_data as $u) {
            $l = 'l'.$u['language_id'];
            if (!$l) {
                $l = LANGUAGE;
            }
            $subject = L\get('CaseBoxNotification', $l).' ';
            $message = (empty($p[$l]) ? '' : '<p>'.$p[$l].'.</p>'); //log message
            switch ($p['action_type']) {
                case 3: // Add case (current info)
                    $subject .= L\get('aboutNewCase', $l);
                    break;
                case 4: // update case (current info)
                    $subject .= L\get('aboutCaseUpdate', $l);
                    break;
                case 7: // close case (current info)
                    $subject .= L\get('aboutCaseClose', $l);
                    break;
                case 21: // add task (current info)+
                case 22: // update task (current info)+
                case 23: // complete task (current info)+
                case 24: // remove task (current info)+
                case 26: // change user status for task
                case 27: // close task (current info)
                case 28: // task overdue
                case 29: // aboutTaskCompletionDecline
                case 30: // aboutTaskCompletionOnBehalt
                case 31: // aboutTaskReopened
                    switch ($p['action_type']) {
                        case 21:
                            $subject = L\get('aboutTaskCreated', $l);
                            break; //CHECKED
                        case 22:
                            $subject = L\get('aboutTaskUpdated', $l);
                            break; //CHECKED
                        case 23:
                            $subject = L\get('aboutTaskComplete', $l);
                            break; //CHECKED
                        case 24:
                            $subject = L\get('aboutTaskDelete', $l);
                            break; // TO BE REWIEWED
                        case 26:
                            $subject = L\get('aboutUserTaskStatusChange', $l);
                            break; // depricated
                        case 27:
                            $subject = L\get('aboutTaskComplete', $l);
                            break;//aboutTaskClose //CHECKED
                        case 28:
                            $subject = L\get('aboutTaskOverdue', $l);
                            break;
                        case 29:
                            $subject = L\get('aboutTaskCompletionDecline', $l);
                            break; //CHECKED
                        case 30:
                            $subject = L\get('aboutTaskCompletionOnBehalt', $l);
                            break; //CHECKED
                        case 31:
                            $subject = L\get('aboutTaskReopened', $l);
                            break; //CHECKED
                    }
                    $sql = 'SELECT t.name
                             ,ti.`path`
                             , u.'.$l.' `owner`
                             , u.name `username`
                        FROM tree t
                        JOIN tree_info ti ON t.id = ti.id
                        JOIN users_groups u ON t.cid = u.id
                        WHERE t.id = $1';
                    $res = DB\dbQuery($sql, $p['task_id']) or die(DB\dbQueryError());
                    if ($r = $res->fetch_assoc()) {
                        $subject = str_replace(
                            array(
                                '{owner}'
                                ,'{name}'
                                ,'{path}'
                            ),
                            array(
                                Util\coalesce($r['owner'], $r['username'])
                                ,$r['name']
                                ,$r['path']
                            ),
                            $subject
                        );
                    }
                    $res->close();
                    $message = Tasks::getTaskInfoForEmail($p['task_id'], $u['id'], @$p["removed_users"]/*, $message/**/);
                    break;
            }
            $p['case_id'] = is_numeric($p['case_id']) ? $p['case_id'] : null;
            $p['object_id'] = is_numeric($p['object_id']) ? $p['object_id'] : null;
            $p['task_id'] = is_numeric($p['task_id']) ? $p['task_id'] : null;
            DB\dbQuery(
                'INSERT INTO notifications (
                    action_type
                    ,case_id
                    ,object_id
                    ,task_id
                    ,subtype
                    ,subject
                    ,message
                    ,time
                    ,user_id)
                VALUES ($1
                    ,$2
                    ,$3
                    ,$4
                    ,0
                    ,$5
                    ,$6
                    ,CURRENT_TIMESTAMP
                    ,$7)',
                array(
                    $p['action_type']
                    ,$p['case_id']
                    ,$p['object_id']
                    ,$p['task_id']
                    ,$subject
                    ,$message
                    ,$u['id']
                )
            ) or die(DB\dbQueryError());
        }
    }
}
