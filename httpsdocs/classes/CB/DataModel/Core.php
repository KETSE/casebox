<?php

namespace CB\DataModel;

use CB\DB;

class Core extends Base
{
    /**
     * database table name
     * @var string
     */
    protected static $tableName = 'cores';

    protected static $tableFields = array(
        'id' => 'int'
        ,'name' => 'varchar'
        ,'cfg' => 'text'
        ,'active' => 'int'
    );

    protected static $decodeJsonFields = array('cfg');

    /**
     * create a core (create database and add core record in __casebox.cores table)
     * @param  array $p
     * @return int   | false
     */
    public static function create($p)
    {
        $rez = false;

        $dbName = \CB\Config::get('prefix') . '_' . $p['name'];

        if (DB\dbQuery('CREATE DATABASE `' . $dbName . '` CHARACTER SET utf8 COLLATE utf8_general_ci')) {
            $rez = parent::create($p);
        }

        return $rez;
    }

    /**
     * read core record form __casebox.cores table
     * @param  varchar $idOrName
     * @return array   | null
     */
    public static function read($idOrName)
    {
        $id = static::toId($idOrName);

        if (!$id) {
            trigger_error('can\'t get core id from name:'.$idOrName, E_USER_WARNING);
        }

        $rez = parent::read($id);

        return $rez;
    }

    /**
     * update core record form __casebox.cores table
     * @param  array $p
     * @return array | null
     */
    public static function update($p)
    {
        if (empty($p['id'])) {
            $p['id'] = static::toId($p['name']);
        }

        $rez = parent::update($p);

        return $rez;
    }

    /**
     * delete core record form __casebox.cores table
     * and drop database
     * @param  varchar $idOrName
     * @return boolean
     */
    public static function delete($idOrName)
    {
        $id = static::toId($idOrName);

        $data = static::read($id);

        $rez = parent::delete($id);

        if ($rez) {
            $dbName = \CB\Config::get('prefix') . '_' . $data['name'];

            DB\dbQuery("DROP DATABASE `$dbName`");
        }

        return $rez;
    }

    public static function getTableName()
    {
        $dbName = \CB\PREFIX . '_casebox';

        return "`$dbName`.`" . static::$tableName . '`';
    }
}
