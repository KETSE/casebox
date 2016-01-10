<?php
namespace CB;

use CB\Util;
use CB\DataModel as DM;
use CB\Templates;

class Templates
{
    /**
     * return templates list
     * @param  array $p
     * @return json  response
     */
    public function readAll($p)
    {
        $p = $p; //dummy codacy assignment
        $rez = DM\Templates::readAllWithData();

        foreach ($rez as &$r) {
            $r['title'] = Util\detectTitle($r['data']);

            unset($r['data']);

            unset($r['cfg']['source']['fn']);
        }

        return $rez;
    }

    public function getTemplatesStructure()
    {
        $rez = array(
            'success' => true
            ,'data' => array()
        );

        $tc = Templates\SingletonCollection::getInstance();
        $tc->loadAll();

        foreach ($tc->templates as $id => $t) {
            $td = $t->getData();
            $tt = $td['type'];

            $fields = $t->getFields();
            foreach ($fields as $f) {

                if (($f['type'] == '_auto_title') && empty($td['title_template'])) {
                    $f['type'] = 'varchar';
                }

                if (($f['type'] == 'geoPoint') && empty($f['cfg']['validator'])) {
                    $f['cfg']['validator'] = 'geoPoint';
                }

                if ($f['pid'] == $id) {
                    $f['pid'] = null;
                }

                //unset server side functions to not be visible on lcient
                if (!empty($f['cfg']['source']['fn'])) {
                    unset($f['cfg']['source']['fn']);
                }

                //set default search conditions:
                // - varchar: contains
                // - objects & multiValued: Contains Any
                // - object & singleValued: Equal
                // - other fieldTypes: equal
                if (($tt == 'search') && empty($f['cfg']['cond'])) {
                    switch ($f['type']) {
                        case 'varchar':
                            $f['cfg']['cond'] = 'contain';
                            break;

                        case '_objects':
                            $f['cfg']['cond'] = empty($f['cfg']['multiValued'])
                                ? '='
                                : '<=';
                            break;

                        default:
                            $f['cfg']['cond'] = '=';
                    }
                }

                // If multiValued=True for Objects field, the default editor=form + default: "renderer": "listObjIcons"
                if (!empty($f['cfg']['multiValued'])) {
                    if (!isset($f['cfg']['editor'])) {
                        $f['cfg']['editor'] = 'form';
                    }
                    if (!isset($f['cfg']['renderer'])) {
                        $f['cfg']['renderer'] = 'listObjIcons';
                    }
                }

                $rez['data'][$id][] = $f;
            }
        }

        return $rez;
    }

    /**
     * runs script for updating solr data for current template items
     * @param  int  $templateId
     * @return json responce
     */
    public function updateSolrData($templateId)
    {
        $rez = array(
            'success' => true
        );

        $tc = Templates\SingletonCollection::getInstance();
        $tpl = $tc->getTemplate($templateId);
        $d = $tpl->getData();

        if (!empty($d['sys_data']['solrConfigUpdated'])) {
            $cmd = 'php -f ' . BIN_DIR . 'update_solr_prepared_data.php -- ' .
                '-c ' . Config::get('core_name'). ' -a -t ' . $templateId . ' &';

            shell_exec($cmd);

            $tpl->setSysDataProperty('solrConfigUpdated');
        }
    }
}
