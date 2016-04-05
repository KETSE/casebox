<?php

namespace CB\Objects\Plugins;

use CB\Objects;
use CB\Util;
use CB\Search;

class ObjectProperties extends Base
{
    public function getData($id = false)
    {
        $rez = parent::getData($id);

        if (empty($rez)) {
            return null;
        }

        $preview = Objects::getPreview($this->id);
        $obj = Objects::getCachedObject($this->id);

        if (empty($obj)) {
            return $rez;
        }

        $data = $obj->getData();

        if (!empty($preview)) {
            $rez['data'] = array(
                'preview' => $preview
            );
        }

        if (!empty($data)) {
            if (!empty($data['pids'])) {
                $path = explode(',', $data['pids']);
                array_pop($path);
                $rez['data']['pids'] = $rez['data']['path'] = implode('/', $path);

                $arr = array(&$rez['data']);
                Search::setPaths($arr);
            }

            foreach ($data as $k => $v) {
                if (in_array(
                    $k,
                    array(
                        'id'
                        ,'template_id'
                        ,'date_end'
                        ,'cid'
                        ,'uid'
                        ,'cdate'
                        ,'udate'
                    )
                )) {
                    if (in_array($k, array('date', 'date_end', 'cdate', 'udate'))) {
                        $v = Util\dateMysqlToISO($v);
                    }

                    $rez['data'][$k] = $v;

                    //add ago udate text
                    if (in_array($k, array('cdate', 'udate'))) {
                        $rez['data'][$k . '_ago_text'] = Util\formatAgoTime($v);
                    }

                }
            }
            $rez['data']['name'] = $obj->getName();
        }

        $rez['data']['can'] = $obj->getActionFlags();

        //set status info for tasks if not active
        if (($obj->getType() == 'task')) {
            $d = &$rez['data'];
            $d['status'] = '';
            switch ($obj->getStatus()) {
                case Objects\Task::$STATUS_ACTIVE:
                    break;

                case Objects\Task::$STATUS_CLOSED:
                    //just add title css class and continue with default
                    $d['titleCls'] = 'task-completed';
                    // break;

                default:
                    $d['status'] = $obj->getStatusText();
                    $d['statusCls'] = $obj->getStatusCSSClass();

            }
        }

        return $rez;
    }
}
