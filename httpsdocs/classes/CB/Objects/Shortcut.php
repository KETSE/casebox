<?php
namespace CB\Objects;

use CB\DB;

class Shortcut extends Object
{

    /**
     * target object id
     * @var int
     */
    protected $targetId = null;

    // public function __construct($id = null, $loadTemplate = false)
    // {
    //     if (is_numeric($id)) {
    //         $this->id = $id;
    //     }
    //     $this->loadTemplate = false;
    // }

    /**
     * create an object with specified params
     * @param  array $p object properties
     * @return int   created id
     */
    public function create($p = false)
    {
        if ($p === false) {
            $p = $this->data;
        }

        // check input params
        if (!isset($p['target_id'])) {
            throw new \Exception("No target id specified for shortcut creation", 1);
        }

        $p['name'] = 'link to #' . $p['target_id'];

        if (empty($p['template_id'])) {
            $p['template_id'] = \CB\Config::get('default_shortcut_template');
        }

        $this->data = $p;

        return parent::create($p);
    }

    /**
     * load template custom data
     */
    protected function loadCustomData()
    {
        parent::loadCustomData();

        $d = &$this->data;

        if (empty($d['target_id'])) {
            return;
        }

        if (empty($d['data'])) {
            $d['data'] = array();
        }

        $res = DB\dbQuery(
            'SELECT tt.type
            FROM tree t
            JOIN templates tt
                ON t.template_id = tt.id
            WHERE t.id = $1',
            $d['target_id']
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $d['target_type'] = $r['type'];
        }
    }

    /**
     * update object
     * @param  array   $p optional properties. If not specified then $this-data is used
     * @return boolean
     */
    public function update($p = false)
    {
        if ($p === false) {
            $p = $this->data;
        }
        $this->data = $p;

        if (empty($p['template_id'])) {
            $p['template_id'] = \CB\Config::get('default_shortcut_template');
        }

        return parent::update($p);
    }
}
