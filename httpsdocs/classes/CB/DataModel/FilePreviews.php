<?php
namespace CB\DataModel;

class FilePreviews extends Base
{
    /**
     * database table name
     * @var string
     */
    protected static $tableName = 'file_previews';

    protected static $tableFields = array(
        'id' => 'int'
        ,'group' => 'varchar'
        ,'status' => 'int'
        ,'filename' => 'varchar'
        ,'size' => 'int'
        ,'cdate' => 'datetime'
        ,'ladate' => 'datetime'
    );
}
