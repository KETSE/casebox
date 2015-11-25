<?php
namespace DisplayColumns;

use CB\Objects;
use CB\Util;
use CB\DataModel as DM;

class ActivityStream extends Base
{

    protected $fromParam = 'activityStream';

    public function onBeforeSolrQuery(&$p)
    {
        $p['rows'] = 15;
        $p['params']['fl'] = array(
            'id', 'pid', 'name', 'template_type', 'target_id', 'oid',
            'cid', 'cdate', 'uid', 'udate', 'comment_user_id', 'comment_date',
            'template_id', 'last_action_tdt'
        );

        $p['params']['sort'] = 'last_action_tdt desc';
        // return parent::onBeforeSolrQuery($p);
    }

    public function onSolrQuery(&$p)
    {
        $result = &$p['result'];
        $data = &$result['data'];
        $actionLogIds = array();

        $comments = new Objects\Plugins\Comments();

        //format ago date and collect log action ids
        foreach ($data as &$doc) {
            $la = Objects::getCachedObject($doc['id'])->getLastActionData();
            $la['agoText'] = Util\formatAgoTime($la['time']);
            $la['uids'] = array_reverse(array_keys($la['users']));
            $doc['lastAction'] = $la;

            $actionLogId = $la['users'][$la['uids'][0]];

            $doc['comments'] = $comments->getData($doc['id']);

            $actionLogIds[$actionLogId] = &$doc;
        }

        $logRecs = DM\Log::getRecords(array_keys($actionLogIds));

        foreach ($logRecs as $r) {
            $d = Util\jsonDecode($r['data']);

            $obj = Objects::getCachedObject($actionLogIds[$r['id']]['id']);
            $diff = $obj->getDiff($d);
            if (!empty($diff)) {
                $html = '';
                foreach ($diff as $fn => $fv) {
                    $html .= "<tr><th>$fn</th><td>$fv</td></tr>";
                }

                $actionLogIds[$r['id']]['diff'] = "<table class=\"as-diff\">$html</table>";
            }
        }
    }

    public function getSolrFields($nodeId = false, $templateId = false)
    {
        // $rez = parent::getSolrFields($nodeId, $templateId);
        $rez = array();

        $rez['sort'] = 'last_action_tdt desc';

        return $rez;
    }

    public function getDC()
    {
        // $rez = parent::getDC();
        $rez = array();

        $rez['sort'] = array(
            'property' => 'last_action_tdt'
            ,'direction' => 'DESC'
        );

        return $rez;
    }

    public function getState($param = null)
    {
        // $rez = parent::getState($param);
        $rez = array();

        $rez['sort'] = array(
            'property' => 'last_action_tdt'
            ,'direction' => 'DESC'
        );

        return $rez;
    }
}
