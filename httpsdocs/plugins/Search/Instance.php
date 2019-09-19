<?php
namespace Search;

class Instance
{
    public function init()
    {
    }

    private $search_helper;

    /**
     * Main search procedure
     *
     * @param $p object
     */
    public function query($p)
    {
        if (!$p) {
            return;
        }

        // prepare search helper
        $this->search_helper = new SearchHelper($p);

        //
        $search_template = $p->grid->template_id;

        // apply search template
        $p->fq[] = 'template_id:'.$this->search_helper->templates[$search_template];

        // apply date filters
        if (isset($this->search_helper->fields['search_date_field'])) {
            if (isset($this->search_helper->fields['search_date_start']) || isset($this->search_helper->fields['search_date_end'])) {
                // field name from thesauri
                switch ($this->search_helper->fields['search_date_field'][0]) {
                    case 5852: // Create date
                        $date_field = 's_date_1';
                        break;
                    case 5856: // Sent date
                        $date_field = 's_date_2';
                        break;
                    case 5857: // Delivered date
                        $date_field = 's_date_3';
                        break;
                }
                $date_start = isset($this->search_helper->fields['search_date_start']) ? $this->search_helper->fields['search_date_start'] : '*';
                $date_end = isset($this->search_helper->fields['search_date_end']) ? $this->search_helper->fields['search_date_end'] : '*';

                $p->fq[] = $date_field.':['.$date_start.' TO '.$date_end.']';
            }
        }
        // apply search
        foreach ($this->search_helper->search as $k => $v) {
            $p->fq[] = $k.':'.$v;
        }

        // custom code

        // return results
        return $this->solrSearch($p, true);
    }

    /**
     * Search with solr & prepare data for output
     *
     * @param $p options
     * @param $prepare bool
     */
    private function solrSearch($p, $prepare = false)
    {
        $prepare = $prepare; //dummy codacy assignment
        // skip grid params
        unset($p->grid);

        $p->fl = 'id, pid, path, name, template_type, system, '.
            'size, date, date_end, oid, cid, cdate, uid, udate, case_id, acl_count, '.
            'case, template_id, task_u_assignee, status, task_d_closed, versions, '.
            'case_violation_resolved, case_violation_unresolved, cfg, type, nid, '.
            'search_hash, case_hash';

        $s = new Search();

        return  $s->query($p);
    }
}
