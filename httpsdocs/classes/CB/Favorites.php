<?php
namespace CB;

class Favorites
{
    public function create($p)
    {
        $rez = array('succes' => true, 'data' => array());
        if (empty($p->data)) {
            return $rez;
        }
        DB\dbQuery(
            'INSERT INTO favorites (user_id, object_id)
            VALUES($1
                 , $2) ON duplicate KEY
            UPDATE object_id = $2',
            array(
                $_SESSION['user']['id']
                ,$p->data->id
            )
        ) or die(DB\dbQueryError());

        $sql = 'SELECT t.id
                 , t.type
                 , t.name
                 , ti.`path`
            FROM favorites f
            JOIN tree t ON f.object_id = t.id
            JOIN tree_info ti on t.id = ti.id
            WHERE f.user_id = $1
                AND object_id = $2';

        $res = DB\dbQuery(
            $sql,
            array(
                $_SESSION['user']['id']
                ,$p->data->id
            )
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            //$r['id'] = intval($r['id']);
            $rez['data'][] = $r;
        }
        $res->close();

        return $rez;
    }
    public function read($p)
    {
        $rez = array('succes' => true, 'data' => array());
        $sql = 'SELECT t.id, t.type, t.name, ti.`path`
            FROM favorites f
            JOIN tree t ON f.object_id = t.id
            JOIN tree_info ti on t.id = ti.id
            WHERE f.user_id = $1';
        $res = DB\dbQuery($sql, $_SESSION['user']['id']) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $rez['data'][] = $r;
        }
        $res->close();

        return $rez;
    }
    public function update($p)
    {
        $rez = array('succes' => true, 'data' => array());

        return $rez;

    }
    public function destroy($p)
    {
        $rez = array('succes' => true, 'data' => array());
        DB\dbQuery(
            'DELETE
            FROM favorites
            WHERE user_id = $1
                AND object_id = $2',
            array(
                $_SESSION['user']['id']
                ,intval($p->data)
            )
        ) or die(DB\dbQueryError());

        return $rez;
    }
}
