<?php
namespace CB\Api;

class Search
{
    /**
     * query method with all params description
     * @param array $p
     *
     *         <will add here all params detailed description>
     *
     * @return json array with a subarray "data" that list all nodes
     */
    public function query($p)
    {
        $search = new \CB\Search();

        $rez = $search->query($p);

        $rez['success'] = true;

        return $rez;
    }
}
