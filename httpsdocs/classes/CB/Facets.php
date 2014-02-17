<?php

namespace CB;

class Facets
{
    public static function getFacetObject($config)
    {
        $type = empty($config['type']) ? 'Strings' : $config['type'];
        $type = '\\CB\\Facets\\'.ucfirst($type).'Facet';

        if (!class_exists($type)) {
            $type = 'CB\\Facets\\StringsFacet';
        }

        return new $type($config);
    }
}
