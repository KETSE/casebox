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

        DB\dbQuery(
            'INSERT INTO action_log (
              `object_id`
              ,`object_pid`
              ,`user_id`
              ,`action_type`
              ,`data`
            ) VALUES ($1, $2, $3, $4, $5)',
            array(
                $data['id']
                ,@$data['pid']
                ,$userId
                ,$p['type']
                ,json_encode($p['logData'], JSON_UNESCAPED_UNICODE)
            )
        );
        $p['action_id'] = DB\dbLastInsertId();

        static::addSolrRecord($p);

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
            'name' => 1
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

        $rez['name'] = htmlspecialchars($rez['name'], ENT_COMPAT);

        if (empty($rez['iconCls'])) {
            $rez['iconCls'] = Browser::getIcon($rez);
        }

        $rez['pids'] = empty($rez['pids'])
            ? Objects::getPids($rez['id'])
            : Util\toNumericArray($rez['pids']);

        $rez['path'] = htmlspecialchars(Util\coalesce($rez['pathtext'], $rez['path']), ENT_COMPAT);

        // setting old and new properties of linear custom data
        if (!empty($p['old'])) {
            $rez['old'] = $p->getLinearData();
        }

        if (!empty($p['new'])) {
            $rez['new'] = $p->getLinearData();
        }

        return $rez;
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

        $record = array(
            'id' => Config::get('core_name') . '_' . $p['action_id']
            ,'core_id' => Config::get('core_id')
            ,'action_id' => $p['action_id']
            ,'action_type' => $p['type']
            ,'action_date' => date('Y-m-d\TH:i:s\Z')
            ,'user_id' => $_SESSION['user']['id']
            ,'object_id' => $data['id']
            ,'object_pid' => empty($data['pid']) ? null : $data['pid']
            ,'object_pids' => $p['logData']['pids']
            ,'object_data' => json_encode($p['logData'], JSON_UNESCAPED_UNICODE)
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
