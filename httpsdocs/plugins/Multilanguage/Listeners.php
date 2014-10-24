<?php
namespace Multilanguage;

use CB\Config;

class Listeners
{
    protected function getNameField()
    {
        return @Config::get('language_settings')[Config::get('user_language')]['name_field'];
    }

    public function onBeforeSolrQuery(&$p)
    {
        $nameField = $this->getNameField();
        if (empty($nameField)) {
            return;
        }

        $sp = &$p['params'];

        $fl = explode(',', $sp['fl']);

        if (!in_array($nameField, $fl)) {
            $fl[] = $nameField;
        }
        $sp['fl'] = implode(',', $fl);
    }

    public function onSolrQuery(&$p)
    {
        $nameField = $this->getNameField();
        if (empty($nameField)) {
            return;
        }

        $sp = &$p['params'];
        $ip = &$p['inputParams'];
        $data = &$p['result']['data'];

        // replace name with non empty $nameField
        foreach ($data as &$doc) {
            if (isset($doc[$nameField])) {
                if (!empty($doc[$nameField])) {
                    $doc['name'] = $doc[$nameField];
                }
                unset($doc[$nameField]);
            }
        }
    }
}
