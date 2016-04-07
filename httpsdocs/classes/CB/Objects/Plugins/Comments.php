<?php

namespace CB\Objects\Plugins;

use CB\Config;
use CB\User;
use CB\DataModel as DM;
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
        return $this->isVisible()
            ? $this->loadMore(array('id' => $id))
            : null;
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

        $prez = parent::getData($p['id']);
        if (empty($prez)) {
            return $rez;
        }

        $commentTemplateIds = DM\Templates::getIdsByType('comment');
        if (empty($commentTemplateIds)) {
            return $rez;
        }

        $limit = empty($p['beforeId']) ? 4 : 10;
        $limit = Config::get('max_load_comments', $limit);

        $params = array(
            'pid' => $this->id
            ,'system' => '[0 TO 2]'
            ,'fq' => array(
                'template_id:('.implode(' OR ', $commentTemplateIds).')'
            )
            ,'fl' => 'id,pid,template_id,cid,cdate,content'
            ,'strictSort' => 'cdate desc'
            ,'rows' => $limit
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

        static::addAttachmentLinks($rez);

        return $rez;
    }

    /**
     * load a single comment by id
     * used for add/update operations on comments
     * @param  int  $id
     * @return json response
     */
    public static function loadComment($id)
    {
        $rez = array(
            'success' => true
            ,'data' => array()
        );

        if (empty($id)) {
            return $rez;
        }

        $params = array(
            'system' => '[0 TO 2]'
            ,'fq' => array(
                'id:'.intval($id)
            )
            ,'fl' => 'id,pid,template_id,cid,cdate,content'
            ,'rows' => 1
        );

        $s = new \CB\Search();
        $sr = $s->query($params);

        foreach ($sr['data'] as $d) {
            $d['cdate_text'] = Util\formatAgoTime($d['cdate']);
            $d['user'] = User::getDisplayName($d['cid'], true);

            //data in solr has already encoded html special chars
            // so we need to decode it and to format the message (where the chars will be encoded again)
            $d['content'] = htmlspecialchars_decode($d['content'], ENT_COMPAT);
            $d['content'] = \CB\Objects\Comment::processAndFormatMessage($d['content']);

            array_unshift($rez['data'], $d);
        }

        static::addAttachmentLinks($rez);

        return @array_shift($rez['data']);
    }

    /**
     * add attachment links below the comments body
     * @param  array reference $rez
     * @return void
     */
    protected static function addAttachmentLinks(&$rez)
    {
        //collect comment ids
        $ids = array();

        foreach ($rez['data'] as $d) {
            $ids[] = $d['id'];
        }

        if (empty($ids)) {
            return;
        }

        //select files for all loaded comments using a single solr request
        $params = array(
            'system' => '[0 TO 2]'
            ,'fq' => array(
                'pid:(' . implode(' OR ', $ids) . ')'
                ,'template_type:"file"'
            )
            ,'fl' => 'id,pid,name,template_id'
            ,'sort' => 'pid,cdate'
            ,'rows' => 50
            ,'dir' => 'asc'
        );

        $s = new \CB\Search();
        $sr = $s->query($params);

        $files = array();
        $fileIds = array();
        $fileTypes = array();

        foreach ($sr['data'] as $d) {
            $files[$d['pid']][] = $d;
            $fileIds[] = $d['id'];
        }

        //get file types
        if (!empty($fileIds)) {
            $fileTypes = DM\Files::getTypes($fileIds);
        }

        foreach ($rez['data'] as &$d) {
            if (empty($files[$d['id']])) {
                continue;
            }

            $links = array();
            foreach ($files[$d['id']] as $f) {
                $f['type'] = @$fileTypes[$f['id']];
                $links[] = static::getFileLink($f);
            }

            $d['files'] = '<ul class="comment-attachments"><li>' . implode('</li><li>', $links) .'</li></ul>';
        }
    }

    /**
     * get link to a file to be displayed in comments
     * @param  array   $file
     * @return varchar
     */
    protected static function getFileLink($file)
    {
        $rez = '';

        if (substr($file['type'], 0, 5) == 'image') {
            $rez = '<a class="click obj-ref" itemid="' . $file['id'] .
                    '" templateid= "' . $file['template_id'] .
                    '" title="' . $file['name'] .
                    '"><img class="fit-img" src="/' . Config::get('core_name') . '/download/' . $file['id'] . '/" /></a>';

        } else {
            $rez = '<a class="click obj-ref icon-padding ' . \CB\Files::getIcon($file['name']) . '" itemid="' . $file['id'] .
                '" templateid= "' . $file['template_id'] .
                '">' . $file['name'] . '</a>';
        }

        return $rez;
    }
}
