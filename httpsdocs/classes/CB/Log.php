<?php
namespace CB;

use \CB\Browser;
use \CB\Util;

class Log
{
    /**
     * add an action to log
     * @param array $p action params
     *     array(
     *       @varchar "type" - action type
     *       ,object "old" - old object
     *       ,object "new" - new object
     *     )
     */
    public static function add(&$p)
    {
        $userId = User::getId();

        //check if log nod disabled by some script
        if (Config::getFlag('disableActivityLog') || empty($userId)) {
            return;
        }

        $data = empty($p['new'])
            ? ( empty($p['old'])
                ? $p['data']
                : $p['old']->getData()
            )
            : $p['new']->getData();

        fireEvent('beforelogadd', $p);

        $p['logData'] = static::getLogData($p);

        $p['activityData'] = static::getActivityData($data);

        DB\dbQuery(
            'INSERT INTO action_log (
              `object_id`
              ,`object_pid`
              ,`user_id`
              ,`action_type`
              ,`data`
              ,`activity_data_db`
            ) VALUES ($1, $2, $3, $4, $5, $6)',
            array(
                $data['id']
                ,@$data['pid']
                ,$userId
                ,$p['type']
                ,Util\jsonEncode($p['logData'])
                ,Util\jsonEncode($p['activityData'])
            )
        );

        $p['action_id'] = DB\dbLastInsertId();

        static::addSolrRecord($p);

        static::adNotificationRecords($p['action_id'], $p['activityData']);

        fireEvent('logadd', $p);
    }

    /**
     * method to get and format the log data as needed
     * @param  array &$p
     * @return array
     */
    protected static function getLogData(&$p)
    {
        $rez = array();

        $fields = array(
            'id' => 1
            ,'name' => 1
            ,'iconCls' => 1
            ,'pids' => 1
            ,'path' => 1
            ,'template_id' => 1
            ,'case_id' => 1
            ,'date' => 1
            ,'size' => 1
            ,'cid' => 1
            ,'oid' => 1
            ,'uid' => 1
            ,'cdate' => 1
            ,'udate' => 1
        );

        $oldData = empty($p['old'])
            ? array()
            : $p['old']->getData();

        $newData = empty($p['new'])
            ? array()
            : $p['new']->getData();

        $oldData = array_intersect_key($oldData, $fields);
        $newData = array_intersect_key($newData, $fields);

        $rez = $newData + $oldData;

        //return empty result for other than object actions (login, logout, etc)
        if (empty($rez['id'])) {
            return;
        }

        Util\unsetNullValues($rez);

        $rez['name'] = htmlspecialchars($rez['name'], ENT_COMPAT);

        if (empty($rez['iconCls'])) {
            $rez['iconCls'] = Browser::getIcon($rez);
        }

        $rez['pids'] = empty($rez['pids'])
            ? Objects::getPids($rez['id'])
            : Util\toNumericArray($rez['pids']);

        $rez['path'] = htmlspecialchars(@Util\coalesce($rez['pathtext'], $rez['path']), ENT_COMPAT);

        switch ($p['type']) {
            case 'comment':
            case 'comment_update':
                $rez['comment'] = $p['comment'];
                break;

            default:
                // setting old and new properties of linear custom data
                if (!empty($p['old'])) {
                    $rez['old'] = $p['old']->getAssocLinearData();
                }

                if (!empty($p['new'])) {
                    $rez['new'] = $p['new']->getAssocLinearData();
                }

                //unset identical values
                if (!empty($rez['old']) && !empty($rez['new'])) {
                    foreach ($rez['old'] as $k => $v) {
                        if (isset($rez['new'][$k]) && ($rez['new'][$k] == $v)) {
                            unset($rez['old'][$k]);
                            unset($rez['new'][$k]);
                        }
                    }
                }
        }

        return $rez;
    }

    /**
     * getActivityData (followers, watchers)
     * @param  array &$d object data
     * @return array
     */
    protected static function getActivityData(&$d)
    {
        $rez = array();

        $userId = User::getId();

        $rez['fu'] = array();

        if (!empty($d['sys_data']['fu'])) {
            $rez['fu'] = array_merge($rez['fu'], $d['sys_data']['fu']);
        }

        $rez['fu'] = array_unique($rez['fu']);
        //remove current user, that caused the action
        $rez['fu'] = array_diff($rez['fu'], array($userId));

        if (!empty($d['sys_data']['wu'])) {
            $rez['wu'] = array_diff($d['sys_data']['wu'], array($userId));
        }

        return $rez;
    }

    /**
     * add notification records for a given action
     * @param  int   $actionId
     * @param  array $activityData array containing "fu" and/or "wu" properties
     * @return void
     */
    private static function adNotificationRecords($actionId, $activityData)
    {
        $users = array();
        if (!empty($activityData['fu'])) {
            foreach ($activityData['fu'] as $uid) {
                $users[intval($uid)] = 0; // email unsent meaning
            }
        }

        if (!empty($activityData['wu'])) {
            foreach ($activityData['wu'] as $uid) {
                $users[intval($uid)] = -1; // email doesnt need to be sent
            }
        }

        //exclude current user from notified users
        unset($users[User::getId()]);

        $sql = 'INSERT INTO notifications
            (object_id, action_id, action_ids, action_type, user_id, email_sent, `read`)

            SELECT l.object_id, l.id, l.id, l.action_type, $2, $3, 0
            FROM action_log l
            WHERE l.id = $1

            ON DUPLICATE KEY

            UPDATE
            action_id = l.id
            ,action_ids = CASE WHEN `read` = 1 THEN l.id ELSE CONCAT(l.id, \',\', action_ids) END
            ,email_sent = $3
            ,`read` = 0';

        foreach ($users as $uid => $uMailSent) {
            DB\dbQuery(
                $sql,
                array(
                    $actionId
                    ,$uid
                    ,$uMailSent
                )
            ) or die(DB\dbQueryError());
        }
    }

    /**
     * add/update record into solr
     * @param array $p action params
     */
    private static function addSolrRecord(&$p)
    {
        $solr = static::getSolrLogConnection();

        if (empty($solr)) {
            \CB\debug('Cant get solr log connection');

            return;
        }

        $data = empty($p['new'])
            ? ( empty($p['old'])
                ? $p['data']
                : $p['old']->getData()
            )
            : $p['new']->getData();

        $fu = @$p['activityData']['fu'];
        $wu = @$p['activityData']['wu'];

        $record = array(
            'id' => Config::get('core_name') . '_' . $p['action_id']
            ,'core_id' => Config::get('core_id')
            ,'action_id' => $p['action_id']
            ,'action_type' => $p['type']
            ,'action_date' => date('Y-m-d\TH:i:s\Z')
            ,'user_id' => User::getId()
            ,'object_id' => $data['id']
            ,'object_pid' => empty($data['pid']) ? null : $data['pid']
            ,'object_pids' => $p['logData']['pids']
            ,'object_data' => Util\jsonEncode($p['logData'])

            // ,'activity_fu' => $fu
            // ,'activity_wu' => $wu
        );

        //delete empty values because solr raises exception when sending empty values for ints
        foreach ($record as $k => $v) {
            if (empty($v)) {
                unset($record[$k]);
            }
        }

        $record['dstatus'] = 0;
        $record['system'] = 0;

        $rez = $solr->addDocument($record);

        $solr->commit();
    }

    public static function getSolrLogConnection()
    {
        $rez = Cache::get('solr_log_connection');

        if (empty($rez)) {
            $cfg = Config::get('action_log');

            if (empty($cfg['core'])) {
                return;
            }

            $rez = new \CB\Search($cfg);

            Cache::set('solr_log_connection', $rez);
        }

        return $rez;
    }
}
