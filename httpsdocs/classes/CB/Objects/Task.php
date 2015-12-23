<?php
namespace CB\Objects;

use CB\DB;
use CB\Util;
use CB\User;
use CB\L;

class Task extends Object
{
    // define possible task status constants
    public static $STATUS_NONE = 0;
    public static $STATUS_OVERDUE = 1;
    public static $STATUS_ACTIVE = 2;
    public static $STATUS_CLOSED = 3;
    public static $STATUS_PENDING = 4; //for dependent tasks

    // define possible user statuses in tasks
    public static $USERSTATUS_NONE = 0; //isn't assigned to task
    public static $USERSTATUS_ONGOING = 1;
    public static $USERSTATUS_DONE = 2;

    /**
     * create a task with specified params
     * @param  array $p object properties
     * @return int   created id
     */
    public function create($p = false)
    {
        if ($p === false) {
            $p = $this->data;
        }
        $this->data = $p;

        $this->setParamsFromData($p);

        return parent::create($p);
    }

    /**
     * load custom data for tasks
     * Note: should be removed after tasks upgraded and custom task tables removed
     */
    protected function loadCustomData()
    {
        parent::loadCustomData();

        $d = &$this->data;

        if (empty($d['data'])) {
            $d['data'] = array();
        }

        // $cd = &$d['data']; //custom data
        // $sd = &$d['sys_data']; //sys_data

        /* add possible action flags*/
        \CB\Tasks::setTaskActionFlags($d);
    }

    /**
     * update object
     * @param  array   $p optional properties. If not specified then $this-data is used
     * @return boolean
     */
    public function update($p = false)
    {
        if ($p === false) {
            $p = $this->data;
        }
        $this->data = $p;

        $this->setParamsFromData($p);

        return parent::update($p);
    }

    /**
     * analize object data and set 'wu' property in sys_data
     *
     * return newly assigned ids
     */
    protected function setFollowers()
    {
        $rez = parent::setFollowers();

        $d = &$this->data;
        $sd = &$d['sys_data'];

        /** add newly assigned users to followers */
        $oldAssigned = array();
        if (!empty($this->oldObject)) {
            $oldAssigned = Util\toNumericArray(@$this->oldObject->getFieldValue('assigned', 0)['value']);
        }
        $newAssigned = Util\toNumericArray(@$this->getFieldValue('assigned', 0)['value']);
        $diff = array_diff($newAssigned, $oldAssigned);
        $wu = empty($sd['wu'])
            ? array()
            : $sd['wu'];

        $wu = array_merge($wu, $diff);

        $rez = array_merge($rez, $newAssigned);

        //analize referenced users from description
        if (!empty($d['data']['description'])) {
            $uids = Util\getReferencedUsers($d['data']['description']);
            if (!empty($uids)) {
                $wu = array_merge($wu, $uids);
                $rez = array_merge($rez, $wu);
            }
        }
        $sd['wu'] = array_unique($wu);

        $rez = array_unique($rez);

        return $rez;
    }

    /**
     * method to collect solr data from object data
     * according to template fields configuration
     * and store it in sys_data under "solr" property
     * @return void
     */
    protected function collectSolrData()
    {
        parent::collectSolrData();

        $d = &$this->data;
        $sd = &$d['sys_data'];
        $solrData = &$sd['solr'];

        $template = $this->getTemplate();

        $solrData['task_status'] = @$sd['task_status'];

        $user_ids = Util\toNumericArray($this->getFieldValue('assigned', 0)['value']);
        if (!empty($user_ids)) {
            $solrData['task_u_assignee'] = $user_ids;
        }

        $user_ids[] = @Util\coalesce($d['oid'], $d['cid']);

        $solrData['task_u_all'] = array_unique($user_ids);

        // $solrData['content'] = @$this->getFieldValue('description', 0)['value'];

        unset($solrData['task_d_closed']);
        unset($solrData['task_ym_closed']);

        if (!empty($sd['task_d_closed'])) {
            $solrData['task_d_closed'] = $sd['task_d_closed'];
            $solrData['task_ym_closed'] = str_replace('-', '', substr($sd['task_d_closed'], 2, 5));
        }

        //get users that didnt complete the task yet
        if (!empty($sd['task_u_done'])) {
            $solrData['task_u_done'] = $sd['task_u_done'];
        }
        if (!empty($sd['task_u_ongoing'])) {
            $solrData['task_u_ongoing'] = $sd['task_u_ongoing'];
        }

        //set class
        $solrData['cls'] = $template->formatValueForDisplay(
            $template->getField('color'),
            $this->getFieldValue('color', 0)['value'],
            false
        );

    }

    /**
     * set "sys_data" params from object data
     * @param array &$p
     */
    protected function setParamsFromData(&$p)
    {
        $d = &$p['data'];

        if (empty($d['sys_data'])) {
            $d['sys_data'] = array();
        }

        $sd = &$p['sys_data'];

        $sd['task_due_date'] = $this->getFieldValue('due_date', 0)['value'];
        $sd['task_due_time'] = $this->getFieldValue('due_time', 0)['value'];

        $sd['task_allday'] = empty($sd['task_due_time']);

        //set date_end to be saved in tree table
        if (empty($sd['task_due_date'])) {
            $p['date_end'] = null;
        } else {
            $p['date_end'] = $sd['task_due_date'];
            if (!$sd['task_allday']) {
                $p['date_end'] = substr($sd['task_due_date'], 0, 10)
                    . ' '
                    . $sd['task_due_time'];
            }
        }

        //set assigned users
        if (empty($sd['task_u_done'])) {
            $sd['task_u_done'] = array();
        }

        $assigned = Util\toNumericArray($this->getFieldValue('assigned', 0)['value']);
        $sd['task_u_ongoing'] = array_diff($assigned, $sd['task_u_done']);

        //set status
        $dateEnd = empty($p['date_end'])
            ? null
            : Util\dateISOToMysql($p['date_end']);

        $status = static::$STATUS_ACTIVE; // active

        if (!empty($sd['task_d_closed'])) {
            $status = static::$STATUS_CLOSED; //closed

        } elseif (!empty($dateEnd)) {
            if (strtotime($dateEnd) < strtotime('now')) {
                $status = static::$STATUS_OVERDUE; //overdue
            }
        }

        $sd['task_status'] = $status;
    }

    // Tasks specific methods

    /**
     * mark the task active
     * @return void
     */
    public function markActive()
    {
        $d = &$this->data;
        $sd = &$d['sys_data'];

        unset($sd['task_d_closed']);

        $this->setParamsFromData($d);

    }

    /**
     * mark the task active, reset done user list and update into db
     * @return void
     */
    public function setActive()
    {
        if (!$this->loaded) {
            $this->load();
        }

        $this->markActive();

        unset($this->data['sys_data']['task_u_done']);

        $this->update();

        $this->logAction('reopen', array('old' => &$this));
    }

    /**
     * mark the task as closed
     * @return void
     */
    public function markClosed()
    {
        $d = &$this->data;
        $sd = &$d['sys_data'];

        $sd['task_status'] = static::$STATUS_CLOSED;
        $sd['task_d_closed'] = date('Y-m-d\TH:i:s\Z');
    }

    /**
     * mark the task as closed and update into db
     * @return void
     */
    public function setClosed()
    {
        if (!$this->loaded) {
            $this->load();
        }

        $this->markClosed();

        $this->logAction('close', array('old' => &$this));
    }

    /**
     * simple function to check if task is closed
     * @return boolean
     */
    public function isClosed()
    {
        return ($this->getStatus() == static::$STATUS_CLOSED);
    }

    /**
     * get task status
     * @return int
     */
    public function getStatus()
    {
        $d = &$this->data;
        $sd = &$d['sys_data'];

        $rez = empty($sd['task_status'])
            ? static::$STATUS_NONE
            : $sd['task_status'];

        return $rez;
    }

    /**
     * get task status text
     * @param  int     $status
     * @return varchar
     */
    public function getStatusText($status = false)
    {
        if ($status === false) {
            $status = $this->getStatus();
        }

        return L\get('taskStatus' . $status, '');
    }

    /**
     * get the css class corresponding for status color
     * @param  int     $status
     * @return varchar | null
     */
    public function getStatusCSSClass($status = false)
    {
        if ($status === false) {
            $status = $this->getStatus();
        }

        $rez = 'task-status';

        switch ($this->getStatus()) {
            case static::$STATUS_OVERDUE:
                $rez .= ' task-status-overdue';
                break;

            case static::$STATUS_ACTIVE:
                $rez .= ' task-status-active';
                break;

            case static::$STATUS_CLOSED:
                $rez .= ' task-status-closed';
                break;
        }

        return $rez;
    }

    /**
     * get user status for loaded task
     * @param int $userId
     */
    public function getUserStatus($userId = false)
    {
        if ($userId == false) {
            $userId = User::getId();
        }

        $d = &$this->data;
        $sd = &$d['sys_data'];

        if (in_array($userId, $sd['task_u_ongoing'])) {
            return static::$USERSTATUS_ONGOING;
        }

        if (in_array($userId, $sd['task_u_done'])) {
            return static::$USERSTATUS_DONE;
        }

        return static::$USERSTATUS_NONE;
    }

    /**
     * change user status for loaded task
     * @param  array   $p params
     * @return boolean
     */
    public function setUserStatus($status, $userId = false)
    {
        $rez = false;
        $action = '';
        $currentUserId = User::getId();

        if ($userId == false) {
            $userId = $currentUserId;
        }

        $d = &$this->data;
        $sd = &$d['sys_data'];

        switch ($status) {
            case static::$USERSTATUS_ONGOING:
                if (in_array($userId, $sd['task_u_done'])) {
                    $sd['task_u_done'] = array_diff($sd['task_u_done'], array($userId));
                    $sd['task_u_ongoing'][] = $userId;
                    unset($sd['task_u_d_closed'][$userId]);

                    $rez = true;

                    $action = ($currentUserId == $userId)
                        ? 'reopen'
                        : 'completion_decline';
                }
                break;
            case static::$USERSTATUS_DONE:
                if (in_array($userId, $sd['task_u_ongoing'])) {
                    $sd['task_u_ongoing'] = array_diff($sd['task_u_ongoing'], array($userId));
                    $sd['task_u_done'][] = $userId;
                    $sd['task_u_d_closed'][$userId] = date(DATE_ISO8601);

                    $rez = true;

                    $action = ($currentUserId == $userId)
                        ? 'complete'
                        : 'completion_on_behalf';
                }
                break;
        }

        if ($rez) {
            $this->checkAutoclose();

            $this->logAction($action, array('old' => &$this, 'forUserId' => $userId));
            // $this->updateSysData();
        }

        return $rez;
    }

    /**
     * check if a task status should be changed after user status change
     */
    public function checkAutoclose()
    {
        $d = &$this->data;
        $sd = &$d['sys_data'];

        if (empty($sd['task_u_ongoing'])) {
            $this->markClosed();
        } else {
            $this->markActive();
        }
    }

    /**
     *  get action flags that a user can do to task
     * @param  int   $userId
     * @return array
     */
    public function getActionFlags($userId = false)
    {
        if ($userId === false) {
            $userId = User::getId();
        }

        $isAdmin = \CB\Security::isAdmin($userId);
        $isOwner = $this->isOwner($userId);
        $isClosed = $this->isClosed();
        $canEdit = !$isClosed && ($isAdmin || $isOwner);

        $rez = array(
            // 'edit' => $canEdit
            'close' => $canEdit
            ,'reopen' => ($isClosed && $isOwner)
            ,'complete' => (!$isClosed && ($this->getUserStatus($userId) == static::$USERSTATUS_ONGOING))
        );

        return $rez;
    }

    /**
     * method to get end date for the task
     * @return varchar | null
     */
    public function getEndDate()
    {
        $rez = null;

        $d = &$this->data;
        $sd = &$d['sys_data'];

        if (!empty($sd['task_due_date'])) {
            $rez = $sd['task_due_date'];
            if (!empty($sd['task_due_time'])) {
                $rez = substr($rez, 0, 11) . $sd['task_due_time'] .'Z';
                $rez = Util\userTimeToUTCTimezone($rez);
            }
        }

        return $rez;
    }

    /**
     * generate html preview for a task
     * @param  int   $id task id
     * @return array
     */
    public function getPreviewBlocks()
    {
        $pb = parent::getPreviewBlocks();

        $data = $this->getData();
        $sd = &$data['sys_data'];

        $template = $this->getTemplate();

        $dateLines = '';
        $ownerRow = '';
        $assigneeRow = '';
        $contentRow = '';

        //create date and status row
        $ed = $this->getEndDate();

        if (!empty($ed)) {
            $endDate = Util\formatTaskTime($ed, !$sd['task_allday']);

            $dateLines = '<tr><td class="prop-key">'.L\get('Due').':</td><td>' . $endDate . '</td></tr>';
            // $dateLine .= '<div class="date">' . $endDate . '</div>';
        }

        if (!empty($sd['task_d_closed'])) {
            $dateLines .= '<tr><td class="prop-key">'.L\get('Completed').':</td><td>' . Util\formatAgoTime($sd['task_d_closed']) . '</td></tr>';
        }

        //create owner row
        $v = $this->getOwner();
        if (!empty($v)) {
            $cn = User::getDisplayName($v);
            $cdt = Util\formatAgoTime($data['cdate']);
            $cd = Util\formatDateTimePeriod($data['cdate'], null, @$_SESSION['user']['cfg']['timezone']);

            $ownerRow = '<tr><td class="prop-key">'.L\get('Owner').':</td><td>' .
                '<table class="prop-val people"><tbody>' .
                '<tr><td class="user"><img class="photo32" src="photo/' . $v . '.jpg?32=' . User::getPhotoParam($v) .
                '" style="width:32px; height: 32px" alt="' . $cn . '" title="' . $cn . '"></td>' .
                '<td><b>' . $cn . '</b><p class="gr">'.L\get('Created') . ': ' .
                '<span class="dttm" title="' . $cd . '">' . $cdt . '</span></p></td></tr></tbody></table>' .
                '</td></tr>';
        }

        //create assignee row
        $v = $this->getFieldValue('assigned', 0);

        if (!empty($v['value'])) {

            $isOwner = $this->isOwner();
            $assigneeRow .= '<tr><td class="prop-key">'.L\get('TaskAssigned').':</td><td><table class="prop-val people"><tbody>';
            $v = Util\toNumericArray($v['value']);

            $dateFormat = \CB\getOption('long_date_format') . ' H:i:s';

            foreach ($v as $id) {
                $un = User::getDisplayName($id);
                $completed = ($this->getUserStatus($id) == static::$USERSTATUS_DONE);

                $cdt = ''; //completed date title
                $dateText = '';

                if ($completed && !empty($sd['task_u_d_closed'][$id])) {
                    $cdt = Util\formatMysqlDate($sd['task_u_d_closed'][$id], $dateFormat);
                    $dateText = ': ' . Util\formatAgoTime($sd['task_u_d_closed'][$id]);
                }

                $assigneeRow .= '<tr><td class="user"><div style="position: relative">'.
                    '<img class="photo32" src="photo/'.$id.'.jpg?32=' . User::getPhotoParam($id).
                    '" style="width:32px; height: 32px" alt="'.$un.'" title="'.$un.'">'.
                ($completed ? '<img class="done icon icon-tick-circle" src="/css/i/s.gif" />': "").
                '</div></td><td><b>'.$un.'</b>'.
                '<p class="gr" title="' . $cdt . '">'.(
                    $completed
                    ? L\get('Completed'). $dateText .
                        ($isOwner ? ' <a class="bt task-action click" action="markincomplete" uid="'.$id.'">'.L\get('revoke').'</a>' : '')
                    : L\get('waitingForAction').
                        ($isOwner ? ' <a class="bt task-action click" action="markcomplete" uid="'.$id.'">'.L\get('complete').'</a>' : '' )
                ).'</p></td></tr>';
            }

            $assigneeRow .= '</tbody></table></td></tr>';
        }

        //create description row
        $v = $this->getFieldValue('description', 0);
        if (!empty($v['value'])) {
            $tf = $template->getField('description');
            $v = $template->formatValueForDisplay($tf, $v);
            $contentRow = '<tr><td class="prop-val" colspan="2">' . $v . '</td></tr>';
        }

        //insert rows
        $p = $pb[0];
        $pos = strrpos($p, '<tbody>');
        $p = substr($p, $pos + 7);
        $pos = strrpos($p, '</tbody>');
        if ($pos !== false) {
            $p = substr($p, 0, $pos);
        }

        $pb[0] = $this->getPreviewActionsRow() .
            '<table class="obj-preview"><tbody>' .
            $dateLines .
            $p .
            $ownerRow .
            $assigneeRow .
            $contentRow .
            '<tbody></table>';

        return $pb;
    }
}
