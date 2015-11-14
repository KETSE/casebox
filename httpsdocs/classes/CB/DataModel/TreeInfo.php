<?php

namespace CB\DataModel;

class TreeInfo extends Base
{
    /**
     * database table name
     * @var string
     */
    protected static $tableName = 'tree_info';

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
        ,'pids' => 'varchar'
        ,'path' => 'varchar'
        ,'case_id' => 'int'
        ,'acl_count' => 'int'
        ,'security_set_id' => 'int'
        ,'updated' => 'int'
    );
}
