<?php
namespace CB\Objects;

use CB\DataModel as DM;
use CB\Objects;

class Config extends Object
{

    /**
     * internal function used by create method for creating custom data
     * @return void
     */
    protected function createCustomData()
    {
        parent::createCustomData();

        $d = &$this->data;
        $dd = &$d['data'];

        $p = array(
            'id' => $this->id
            ,'pid' => empty($d['pid'])
                ? null
                : $this->getDMPid($d['pid'])
            ,'param' => $dd['_title']
            ,'value' => empty($dd['value'])
                ? ''
                : $dd['value']
        );

        if (isset($dd['order'])) {
            $p['order'] = $dd['order'];
        }

        DM\Config::create($p);
    }

    /**
     * update objects custom data
     * @return void
     */
    protected function updateCustomData()
    {
        parent::updateCustomData();

        $d = &$this->data;
        $dd = &$d['data'];

        $p = array(
            'id' => $d['id']
            ,'pid' => empty($d['pid'])
                ? null
                : $this->getDMPid($d['pid'])
            ,'param' => $dd['_title']
            ,'value' => empty($dd['value'])
                ? ''
                : $dd['value']
        );

        if (isset($dd['order'])) {
            $p['order'] = $dd['order'];
        }

        if (DM\Config::exists($d['id'])) {
            DM\Config::update($p);
        } else {
            DM\Config::create($p);
        }
    }

    public function delete($permanent = false)
    {
        //always delete config elements permanently
        parent::delete(true);
    }

    protected function deleteCustomData($permanent)
    {
        if ($permanent) {
            $d = &$this->data['data'];

            DM\Config::delete(DM\Config::toId($d['_title'], 'param'));
        }

        parent::deleteCustomData($permanent);
    }

    /**
     * get data model pid that is different from tree one
     * @param  int $pid
     * @return int
     */
    protected function getDMPid($pid)
    {
        $rez = null;

        $r = DM\Config::read($pid);

        if (!empty($r)) {
            $rez = $r['id'];
        }

        return $rez;
    }

    protected function moveCustomDataTo($targetId)
    {
        DM\Config::update(
            array(
                'id' => $this->id
                ,'pid' => $targetId
            )
        );
    }
}
