<?php
namespace CB;

class Thesauri
{
    public function create($p)
    {
        return array('success' => true, 'data' => $p);
    }

    public function read($p)
    {
        $params = ($p && !empty($p->thesauriId)) ? ' and pid = '.intval($p->thesauriId) : '';
        $sql = 'SELECT t.id
                 , t.pid
                 , t.l'.USER_LANGUAGE_INDEX.' `name`
                                            , t.`order`
                                            , t.iconCls
            FROM tags t
            WHERE t.hidden IS NULL '.$params.'
            ORDER BY pid
                   , `order`
                   , 3';
        $data = array();
        $res = DB\dbQuery($sql) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $data[] = $r;
        }
        $res->close();

        return array('success' => true, 'data' => $data);
    }

    public function update($p)
    {
        return array('success' => true, 'data' => $p);
    }

    public function destroy($p)
    {
        return array('success' => true, 'data' => $p);
    }
}
