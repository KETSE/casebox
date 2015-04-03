<?php

namespace CB\Objects\Plugins;

use CB\Config;
use CB\User;
use CB\Util;

class Comments extends Base
{

    /**
     * get default set of comments for given object id
     * @param  int  $id
     * @return json response
     */
    public function getData($id = false)
    {
        return $this->loadMore(array('id' => $id));
    }

    /**
     * load next set of comments (not all are loaded by default)
     * @param  array $p
     * @return json  response
     */
    public function loadMore($p)
    {
        $rez = array(
            'success' => true
            ,'data' => array()
        );

        if (empty(parent::getData($p['id']))) {
            return $rez;
        }

        $commentTemplateIds = \CB\Templates::getIdsByType('comment');
        if (empty($commentTemplateIds)) {
            return $rez;
        }

        $limit = Config::get('max_load_comments', 4);

        $params = array(
            'pid' => $this->id
            ,'system' => '[0 TO 2]'
            ,'fq' => array(
                'template_id:('.implode(' OR ', $commentTemplateIds).')'
            )
            ,'fl' => 'id,pid,template_id,cid,cdate,content'
            ,'sort' => 'cdate'
            ,'rows' => $limit
            ,'dir' => 'desc'
        );

        if (!empty($p['beforeId']) && is_numeric($p['beforeId'])) {
            $params['fq'][] = 'id:[* TO ' . ($p['beforeId'] - 1) . ']';
        }

        $s = new \CB\Search();
        $sr = $s->query($params);

        if (empty($p['beforeId'])) {
            $rez['total'] = $sr['total'];
        }

        foreach ($sr['data'] as $d) {
            $d['cdate_text'] = Util\formatAgoTime($d['cdate']);
            $d['user'] = User::getDisplayName($d['cid'], true);

            //data in solr has already encoded html special chars
            // so we need to decode it and to format the message (where the chars will be encoded again)
            $d['content'] = htmlspecialchars_decode($d['content'], ENT_COMPAT);
            $d['content'] = \CB\Objects\Comment::processAndFormatMessage($d['content']);

            array_unshift($rez['data'], $d);
        }

        return $rez;

    }
}
