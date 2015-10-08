<?php

namespace CB\Facets;

class CalendarFacet extends StringsFacet
{
    public function getTitle()
    {
        return \CB\L\get('Calendar');
    }

    public function getSolrParams()
    {
        return array();
    }

    public function getClientData($options = array())
    {
        $rez = parent::getClientData();

        $rez['type'] = 'calendar';

        return $rez;
    }
}
