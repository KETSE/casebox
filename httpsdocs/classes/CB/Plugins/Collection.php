<?php
namespace CB\Plugins;

use CB\DB as DB;
use CB\Util as Util;

/**
 * Templates collection class
 */
class Collection
{
    /**
     * array of \CB\Plugin classes
     * @var array
     */
    public $items = array();

    /**
     * load all plugins from database
     *
     * @return void
     */
    public function loadAll()
    {
        if (!empty($this->loaded)) {
            return $this->items;
        }

        $this->items = array();

        $res = DB\dbQuery(
            'SELECT id
                ,name
                ,cfg
                ,`active`
                ,`order`
            FROM casebox.plugins
            ORDER BY `order`'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $r['cfg'] = Util\toJSONArray($r['cfg']);
            $this->items[$r['name']] = $r;
        }
        $res->close();
        $this->loaded = true;
    }

    /**
     * get plugin data by its name
     *
     * @return array
     */
    public function getData($name)
    {
        $this->loadAll();
        if (!empty($this->items[$name])) {
            return $this->items[$name];
        }

        return null;
    }

    /**
     * get active plugin list as an associative array ($pluginName => $pluginConfig)
     * @return varchar
     */
    public function getActivePlugins()
    {
        $rez = array();
        $this->loadAll();

        foreach ($this->items as $name => $data) {
            if ($data['active'] == 1) {
                $rez[$name] = $data['cfg'];
            }
        }

        return $rez;
    }
}
