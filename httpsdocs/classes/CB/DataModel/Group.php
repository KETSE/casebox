<?php

namespace CB\DataModel;

use CB\DB;

class Group extends Users
{
    /**
     * db value for type field
     * @var integer
     */
    protected static $type = 1;
}
