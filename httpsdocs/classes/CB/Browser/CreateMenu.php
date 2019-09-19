<?php
namespace CB\Browser;

use CB\DB;
use CB\DataModel as DM;
use CB\Util;
use CB\Search;
use CB\Objects;
use CB\Cache;

class CreateMenu
{
    /**
     * get the menu config for a given path or id
     * @param  varchar | int $path path string or node id
     * @return [type]        [description]
     */
    public static function getMenuForPath($path)
    {
        $rez = '';

        //get item path if id specified
        if (is_numeric($path)) {
            $tmp = \CB\Path::getPath($path);
            $path = '/' . $tmp['path'];
        }

        if (is_string($path)) {
            $path = explode('/', $path);
        }

        $path = array_reverse(array_filter($path, 'is_numeric'));
        $path = Util\toNumericArray($path);

        // get templates for each path elements
        $nodeTemplate = array();

        $recs = DM\Tree::readByIds($path);
        foreach ($recs as $r) {
            $nodeTemplate[$r['id']] = $r['template_id'];
        }

        //get db menu into variable
        $menu = static::getMenuRules();

        $ugids = isset($_SESSION['user']['groups'])
            ? $_SESSION['user']['groups']
            : array();

        $ugids[] = $_SESSION['user']['id'];

        // we have 3 main criterias for detecting needed menu:
        //  - user_group_ids - records for specific users or groups
        //  - node_ids
        //  - template_ids
        //
        // we'll iterate the path from the end and detect the menu

        $lastWeight = 0;
        for ($i=0; $i < sizeof($path); $i++) {
            //firstly we'll check if we find a menu row with id or template of the node
            foreach ($menu as $m) {
                $weight = 0;

                if (in_array($path[$i], $m['nids'])) {
                    $weight += 50;
                } elseif (empty($m['nids'])) {
                    $weight += 1;
                } else {
                    //skip this record because it contain nids and not contain this node id
                    continue;
                }

                if (in_array($nodeTemplate[$path[$i]], $m['ntids'])) {
                    $weight += 50;
                } elseif (empty($m['ntids'])) {
                    $weight += 1;
                } else {
                    //skip this record because it has ntids specified and not contain this node template id
                    continue;
                }

                if (empty($m['ugids'])) {
                    $weight += 1;
                } else {
                    $int = array_intersect($ugids, $m['ugids']);
                    if (empty($int)) {
                        continue;
                    } else {
                        $weight += 10;
                    }
                }

                if ($weight > $lastWeight) {
                    $lastWeight = $weight;
                    $rez = $m['menu'];
                }
            }
            //if nid matched or template matched then dont iterate further
            if ($lastWeight > 50) {
                return $rez;
            }
        }

        return $rez;
    }

    protected static function getMenuRules()
    {
        $rez = Cache::get('CreateMenuRules', array());

        if (!empty($rez)) {
            return $rez;
        }

        $s = new Search();
        $ids = array();

        $sr = $s->query(
            array(
                'fl' => 'id',
                'template_types' => 'menu',
                'skipSecurity' => true
            )
        );

        foreach ($sr['data'] as $r) {
            $ids[] = $r['id'];
        }

        $arr = Objects::getCachedObjects($ids);

        foreach ($arr as $o) {
            $d = $o->getData()['data'];
            $rez[] = array(
                'nids' => empty($d['node_ids'])
                    ? array()
                    : Util\toNumericArray($d['node_ids'])
                ,'ntids' => empty($d['template_ids'])
                    ? array()
                    : Util\toNumericArray($d['template_ids'])
                ,'ugids' => empty($d['user_group_ids'])
                    ? array()
                    : Util\toNumericArray($d['user_group_ids'])
                ,'menu' => @$d['menu']
            );
        }

        Cache::set('CreateMenuRules', $rez);

        return $rez;
    }
}
