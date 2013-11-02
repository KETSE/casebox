<?php
namespace CB;

class Path
{
    /* get last element di from a path or return root folder id if no int element is found */
    public static function getId($path = '')
    {
        $path = trim($path);
        while (!empty($path) && (substr($path, -1) == '/')) {
            $path = substr($path, 0, strlen($path)-1);
        }
        $id = explode('/', $path);
        $id = array_pop($id);
        $id = is_numeric($id) ? $id : Browser::getRootFolderId();

        return $id;
    }

    public static function getPath($id)
    {
        $rez = array('success' => false);
        if (!is_numeric($id)) {
            return $rez;
        }
        $res = DB\dbQuery(
            'SELECT pids FROM tree_info WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $r['pids'] = str_replace(',', '/', $r['pids']);
            $rez = array('success' => true, 'id' => $id, 'path' => $r['pids']);
        }
        $res->close();

        return $rez;
    }

    public static function getPidPath($id)
    {
        $rez = array('success' => false);
        if (!is_numeric($id)) {
            return $rez;
        }
        $res = DB\dbQuery(
            'SELECT ti.pids
            FROM tree t
            JOIN tree_info ti ON t.id = ti.id
            WHERE t.id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $r['pids'] = explode(',', $r['pids']);
            array_pop($r['pids']);
            $r['pids'] = implode('/', $r['pids']);
            $rez = array('success' => true, 'id' => $id, 'path' => $r['pids']);
        }
        $res->close();

        return $rez;
    }

    /**
     * return textual repsentation of an ids path
     * @param  varchar | array $p direct path string or an array containig 'path' index defined
     * @return varchar
     */
    public static function getPathText($p)
    {
        $path = '';
        if (is_array($p)) {
            if (isset($p['path'])) {
                $path = $p['path'];
            }
        } else {
            $path = '';
        }

        if (empty($path)) {
            $path = '/';
        }

        while ($path[0] == '/') {
            $path = substr($path, 1);
        }
        $path = explode('/', $path);
        $ids = array_filter($path, 'is_numeric');
        $id = array_pop($ids);
        $res = DB\dbQuery('SELECT pids from tree_info WHERE id = $1', $id) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $path = explode(',', $r['pids']);
            if (!empty($path) && empty($path[0])) {
                array_shift($path);
            }
            array_shift($path);
            $ids = $path;
        }
        $res->close();

        if (empty($path)) {
            return '/';
        }
        if ($path[0] == Browser::getRootFolderId()) {
            array_shift($path);
        }
        if (empty($ids)) {
            return '/';
        }

        $names = array();
        $res = DB\dbQuery(
            'SELECT id
                ,name
            FROM tree
            WHERE id IN ('.implode(', ', $ids).')'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $names[$r['id']] = $r['name'];
        }
        $res->close();
        $rez = array();
        for ($i=0; $i < sizeof($path); $i++) {
            if (isset($names[$path[$i]])) {
                if ((substr($names[$path[$i]], 0, 1) == '[') && (substr($names[$path[$i]], -1, 1) == ']')) {
                    $names[$path[$i]] = Util\coalesce(
                        L\get(substr($names[$path[$i]], 1, strlen($names[$path[$i]]) -2)),
                        $names[$path[$i]]
                    );
                }
                $rez[] = $names[$path[$i]];
            } else {
                $rez[] = $path[$i];
            }
        }

        return '/'.implode('/', $rez);
    }

    /**
     * return generic properties for a path of ids
     * @param  varchar | array $p direct path string or an array containig 'path' index defined
     * @return varchar
     */
    public static function getPathProperties($p)
    {
        $path = '';
        if (is_array($p)) {
            if (isset($p['path'])) {
                $path = $p['path'];
            }
        } else {
            $path = $p;
        }

        if (empty($path)) {
            $path = '/';
        }
        while ($path[0] == '/') {
            $path = substr($path, 1);
        }
        $path = explode('/', $path);
        $ids = array_filter($path, 'is_numeric');
        if (empty($ids)) {
            $ids = array(Browser::getRootFolderId());
            $path = $ids;
        }
        $rez = array();
        $lastId = array_pop($ids);
        $res = DB\dbQuery(
            'SELECT t.id
                ,t.name
                ,t.`system`
                ,t.`type`
                ,ti.pids `path`
                ,ti.`case_id`
                ,t.`template_id`
                ,tt.`type` template_type
            FROM tree t
            JOIN tree_info ti on t.id = ti.id
            LEFT JOIN templates tt ON t.template_id = tt.id
            WHERE t.id = $1',
            $lastId
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $r['path'] = str_replace(',', '/', $r['path']);
            $rez = $r;
        }
        $res->close();

        return $rez;
    }

    /**
     * tree nodes can contain Translation variable in place of name like: [MyDocuments]
     * @param  vrchar  $path
     * @return varchar
     */
    public static function replaceCustomNames($path)
    {
        $path = explode('/', $path);
        for ($i=0; $i < sizeof($path); $i++) {
            if ((substr($path[$i], 0, 1) == '[') && (substr($path[$i], -1) == ']')) {
                $l = substr($path[$i], 1, mb_strlen($path[$i])-2);
                $path[$i] = Util\coalesce(L\get($l), $l);
            }
        }
        $path = implode('/', $path);

        return $path;
    }
}
