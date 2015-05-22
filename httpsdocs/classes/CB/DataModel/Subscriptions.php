<?php

namespace CB\DataModel;

use CB\DB;

class Subscriptions
{
    /**
     * create a subscription record
     * @param array $p array with following properties:
     *                  group - optional, default 'db'
     *                  user_id
     *                  object_id
     *                  type (watch, follow, ignore)
     * @return int created id
     */
    public static function create($p)
    {
        //validate input params
        static::validateParams('create', $p);

        //prepare params
        $group = empty($p['group'])
            ? 'db'
            : $p['group'];

        if (empty($p['object_id'])) {
            $p['object_id'] = null;
        }

        //create database record
        DB\dbQuery(
            'INSERT INTO subscriptions
            (group, user_id, object_id, type)
            VALUES ($1, $2, $3, $4)',
            array(
                $group
                ,$p['user_id']
                ,$p['object_id']
                ,$p['type']
            )
        ) or die(DB\dbQueryError());
    }

    /**
     * read subscription record from db by id
     * @param  int   $id
     * @return array | null
     */
    public static function read($id)
    {
        $rez= null;

        //validate input params
        static::validateParams('create', $id);

        //read
        $res = DB\dbQuery(
            'SELECT *
            FROM subscriptions
            WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r;
        }

        $res->close();

        return $rez;
    }

    /**
     * update a subscription record
     * @param array $p array with following properties:
     *                  id
     *                  group - optional, default 'db'
     *                  user_id
     *                  object_id
     *                  type (watch, follow, ignore)
     * @return boolean
     */
    public static function update($p)
    {
        //validate input params
        static::validateParams('update', $p);

        //prepare params
        $params = $p + array('group' => 1, 'user_id' => 1, 'object_id' => 1);
        $fields = array();

        $i = 1;
        foreach ($params as $k => $v) {
            $fields[] = $k . ' = $' . $i;

            $i++;
        }

        $sql = 'UPDATE subscriptions
            SET ' . implode(', ', $fields) . '
            WHERE id = ' . $p['id'];

        //update database record
        DB\dbQuery(
            $sql,
            array_values($params)
        ) or die(DB\dbQueryError());

    }

    /**
     * delete a subscription by its id
     * @param  int     $id
     * @return boolean
     */
    public static function delete($id)
    {
        static::validateParams('delete', $p);

        DB\dbQuery(
            'DELETE FROM subscriptions
            WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());
    }

    /**
     * validate params for given operation
     * @param  varchar $operation
     * @param  variant $p
     * @return void    throws an exception on error
     */
    protected static function validateParams($operation, $p)
    {
        $rez = true;
        switch ($operation) {
            case 'update':
                //check only if valid id for update
                if (empty($p['id']) || !is_numeric($p['id'])) {
                    $rez = false;
                    continue;
                }
                //continue cheching the rest of params as for create

            case 'create':
                if (!empty($p['group']) && !in_array($p['group'], array('db', 'query', 'system'))) {
                    $rez = false;
                    continue;
                }
                if (empty($p['user_id']) || !is_numeric($p['user_id'])) {
                    $rez = false;
                    continue;
                }

                //allow the object_id to be empty (for global user settings)
                if (!empty($p['object_id']) && !is_numeric($p['object_id'])) {
                    $rez = false;
                    continue;
                }

                if (empty($p['type']) || !in_array($p['type'], array('watch', 'follow', 'ignore'))) {
                    $rez = false;
                    continue;
                }
                break;

            case 'read':
            case 'delete':
                if (empty($p) || !is_numeric($p)) {
                    $rez = false;
                    continue;
                }

                break;
        }

        if (!$rez) {
            trigger_error(L\get('ErroneousInputData'), E_USER_ERROR);
        }
    }
}
