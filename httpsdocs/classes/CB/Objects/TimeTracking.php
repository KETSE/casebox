<?php
namespace CB\Objects;

use CB\Objects;
use CB\DataModel as DM;
use CB\Log;

class TimeTracking extends Object
{

    /**
     * create method
     * @return void
     */
    public function create($p = false)
    {
        if ($p === false) {
            $p = &$this->data;
        }

        $p['data']['cost'] = $this->getTimeCost();

        //disable default log from parent Object class
        \CB\Config::setFlag('disableActivityLog', true);

        $rez = parent::create($p);

        \CB\Config::setFlag('disableActivityLog', false);

        $this->addParentSpentTime();

        return $rez;
    }

    /**
     * update
     * @param  array   $p optional properties. If not specified then $this-data is used
     * @return boolean
     */
    public function update($p = false)
    {
        if ($p === false) {
            $p = &$this->data;
        }

        $p['data']['cost'] = $this->getTimeCost();

        //disable default log from parent Object class
        \CB\Config::setFlag('disableActivityLog', true);

        $rez = parent::update($p);

        \CB\Config::setFlag('disableActivityLog', false);

        $this->recalculateParentSpentTime();

        return $rez;

    }

    public function delete($persistent = false)
    {
        if (!$this->loaded) {
            $this->load();
        }

        parent::delete($persistent);

        $this->recalculateParentSpentTime();
    }

    protected function getTimeCost()
    {
        $rez = 10;

        $eventParams = [
            'object' => &$this,
            'result' => &$rez
        ];

        \CB\fireEvent('onGetTimeCost', $eventParams);

        return $rez;
    }

    protected function collectSolrData()
    {
        $rez = parent::collectSolrData();

        $spent = $this->getSpentTime();

        $this->data['sys_data']['solr']['time_spent_money_f'] = $spent['money'];

    }

    /**
     * get spent time for current object
     * @return void
     */
    protected function getSpentTime()
    {
        $p = &$this->data;
        $d = &$p['data'];

        $time = empty($d['time_spent'])
            ? '0'
            : $d['time_spent'];

        $cost = empty($d['cost'])
            ? 0
            : intval($d['cost']);

        $time = explode(':', $time);

        $seconds = array_shift($time) * 60 * 60;

        if (!empty($time)) {
            $seconds += array_shift($time) * 60;
        }

        if (!empty($time)) {
            $seconds += array_shift($time);
        }

        return [
            'sec' => $seconds,
            'cost' => $cost,
            'money' => ($seconds / 60 / 60) * $cost
        ];
    }

    /**
     * get spent time from parent object
     * @return void
     */
    protected function getParentSpentTime()
    {
        $po = $this->getParentObject();
        $posd = $po->getSysData();

        $newUserIds = array();

        $spentTime = empty($posd['spentTime'])
            ? []
            : $posd['spentTime'];

        $seconds = empty($spentTime['sec'])
            ? 0
            : intval($spentTime['sec']);
        $cost = empty($spentTime['money'])
            ? 0
            : intval($spentTime['money']);

        return [
            'sec' => $seconds,
            'money' => $cost
        ];
    }

    /**
     * update spennt time for parent object
     * @return void
     */
    protected function setParentSpentTime($spentTime)
    {
        $po = $this->getParentObject();
        $posd = $po->getSysData();

        $posd['spentTime'] = $spentTime;

        $po->updateSysData($posd);
    }

    /**
     * add spent time to parent object
     * @return void
     */
    protected function addParentSpentTime()
    {
        $parentSpentTime = $this->getParentSpentTime();
        $spentTime = $this->getSpentTime();

        $parentSpentTime['sec'] += $spentTime['sec'];
        $parentSpentTime['money'] += $spentTime['money'];

        $this->setParentSpentTime($parentSpentTime);
    }

    /**
     * recalculate spent time from parent object
     * @return void
     */
    protected function recalculateParentSpentTime()
    {
        $recs = DM\Objects::getChildrenByTemplate(
            $this->data['pid'],
            $this->data['template_id']
        );

        $rez = [
            'sec' => 0,
            'money' => 0
        ];

        foreach ($recs as $r) {
            $rez['sec'] += $r['sys_data']['solr']['time_spent_i'];
            $rez['money'] += $r['sys_data']['solr']['time_spent_money_f'];
        }

        $this->setParentSpentTime($rez);

        return $rez;
    }
}
