<?php

namespace CB\Objects\Plugins;

use CB\User;
use CB\Util;
use CB\Objects;

class ContentItems extends Base
{

    public function getData($id = false)
    {
        $rez = array(
            'success' => true
        );

        if (empty(parent::getData($id))) {
            return $rez;
        }

        if (!$this->isVisible()) {
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

    /**
     * check if current plugin is visible according to its config
     * @return boolean
     */
    protected function isVisible()
    {
        $rez = true;

        $config = $this->config;
        if (!empty($config['fn']['visibility'])) {
            $rez = $this->getFunctionResult($config['fn']['visibility']);

        } elseif (!empty($config['visibility'])) {
            $obj = Objects::getCachedObject($this->id);
            if (!empty($obj)) {
                foreach ($config['visibility'] as $fn => $fv) {
                    if (is_scalar($fv)) {
                        $fv = [$fv];
                    }

                    $val = @$obj->getFieldValue($fn, 0)['value'];
                    $fieldRez = false;

                    foreach ($fv as $v) {
                        if (is_numeric($v)) {
                            $arr = Util\toNumericArray($val);
                            $fieldRez = $fieldRez || in_array($v, $arr);
                        } else {
                            $fieldRez = $fieldRez || ($v == $val);
                        }
                    }

                    $rez = $rez && $fieldRez;
                }
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

        //if config is empty - use old behavior
        if (empty($config)) {
            $rez['pid'] = $this->id;
            $rez['fq'] = ['(template_type:object) OR (target_type:object)'];

            $folderTemplates = \CB\Config::get('folder_templates');
            if (!empty($folderTemplates)) {
                $rez['fq'][] = '!template_id:(' .
                    implode(' OR ', Util\toNumericArray($folderTemplates)) . ')';
            }

        } elseif (!empty($config['fn']['source'])) {
            $ids = $this->getFunctionResult($config['fn']['source']);
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
