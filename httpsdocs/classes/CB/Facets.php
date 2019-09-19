<?php

namespace CB;

class Facets
{
    public static function getFacetObject($config)
    {
        $type = 'CB\\Facets\\StringsFacet';

        if (!empty($config['type'])) {
            $configType = '\\CB\\Facets\\'.ucfirst($config['type']).'Facet';

            if (class_exists($configType)) {
                $type = $configType;
            }
        }

        return new $type($config);
    }
}
