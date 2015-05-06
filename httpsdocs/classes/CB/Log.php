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
        //check if log nod disabled by some script

        if (Config::getFlag('disableActivityLog') ||
            empty($_SESSION['user']['id'])
        ) {
            return;
        }

        $data = empty($p['new'])
            ? ( empty($p['old'])
                ? $p['data']
                : $p['old']->getData()
            )
            : $p['new']->getData();

        fireEvent('beforelogadd', $p);

        $p['logData'] = @array(
            'name' => htmlspecialchars($data['name'], ENT_COMPAT)
            ,'iconCls' => Browser::getIcon($data)
            ,'pids' => empty($data['pids'])
                ? Objects::getPids($data['id'])
                : Util\toNumericArray($data['pids'])
            ,'path' => htmlspecialchars(Util\coalesce($data['pathtext'], $data['path']), ENT_COMPAT)
            ,'template_id' => $data['template_id']
            ,'case_id' => $data['case_id']
            ,'date' => $data['date']
            ,'size' => $data['size']
            ,'cid' => $data['cid']
            ,'oid' => $data['oid']
            ,'uid' => @$data['uid']
            ,'cdate' => $data['cdate']
            ,'udate' => $data['udate']
        );

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
                ,$_SESSION['user']['id']
                ,$p['type']
                ,json_encode($p['logData'], JSON_UNESCAPED_UNICODE)
            )
        );
        $p['action_id'] = DB\dbLastInsertId();

        static::addSolrRecord($p);

        fireEvent('logadd', $p);
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
