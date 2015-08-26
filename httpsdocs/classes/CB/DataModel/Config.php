<?php

namespace CB\DataModel;

class Config extends Base
{
    /**
     * database table name
     * @var string
     */
    protected static $tableName = 'config';

    /**
     * available table fields
     *
     * associative array of fieldName => type
     * that is also used for trivial validation of input values
     *
     * @var array
     */
    protected static $tableFields = array(
        'id' => 'int'
        ,'param' => 'varchar'
        ,'value' => 'text'
    );
}
