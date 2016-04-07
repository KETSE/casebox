<?php

namespace CB\Objects\Plugins;

use CB\User;
use CB\Util;
use CB\Objects;

class ContentItems extends Base
{

    public function getData($id = false)
    {
        if (!$this->isVisible()) {
            return null;
        }

        $rez = array(
            'success' => true,
            'data' => []
        );

        if(empty($this->id)) {
            return $rez;
        }

        $params = $this->getSolrParams();

        $s = new \CB\Search();
        $sr = $s->query($params);
        foreach ($sr['data'] as $d) {
            $d['ago_text'] = Util\formatAgoTime($d['cdate']);
            $d['user'] = @User::getDisplayName($d['cid']);
            $rez['data'][] = $d;
        }

        //send additional config params
        $config = $this->config;
        if (isset($config['limit'])) {
            $rez['limit'] = $config['limit'];
        }

        if (!empty($config['header'])) {
            $h = $config['header'];
            $title = empty($h['title'])
                ? ''
                : Util\detectTitle($h['title']);

            if (!empty($h['showTotal'])) {
                $title .= ' ({total})';
            }

            $rez['title'] = $title;

            if (!empty($h['menu'])) {
                $rez['menu'] = $h['menu'];
            }
        }

        return $rez;
    }

    protected function getSolrParams()
    {
        $rez = [
            'fl' => 'id,pid,name,template_id,cdate,cid'
            ,'sort' => 'cdate desc'
        ];

        $config = $this->config;

        if (!empty($config['fn'])) {
            $ids = $this->getFunctionResult($config['fn']);
            if (!empty($ids)) {
                $rez['fq'] = 'id:(' . implode(' OR ', $ids) . ')';
            }

        } elseif (isset($config['fq'])) {
            $fq = str_replace('$id', $this->id, $config['fq']);
            $matches = [];
            preg_match_all('/\$([\w]+)/', $fq, $matches);
            if (!empty($matches[1])) {
                $obj = Objects::getCachedObject($this->id);
                foreach ($matches[1] as $fn) {
                    $v = @$obj->getFieldValue($fn, 0)['value'];
                    if (empty($v)) {
                        $v = 0;
                    }
                    $fq = str_replace('$' . $fn, $v, $fq);
                }
            }

            $rez['fq'] = $fq;

        } else {//if config is empty - use old behavior
            $rez['pid'] = $this->id;
            $rez['fq'] = ['(template_type:object) OR (target_type:object)'];

            $folderTemplates = \CB\Config::get('folder_templates');
            if (!empty($folderTemplates)) {
                $rez['fq'][] = '!template_id:(' .
                    implode(' OR ', Util\toNumericArray($folderTemplates)) . ')';
            }
        }

        if (!empty($config['sort'])) {
            $rez['sort'] = $config['sort'];
        }

        return $rez;
    }

    protected function getFunctionResult($fn)
    {
        $t = explode('.', $fn);
        $class = new $t[0];
        $method = $t[1];

        return $class->$method($this->id);
    }
}
