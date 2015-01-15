<?php
namespace DisplayColumns;

use CB\State;

class Grid extends Base
{

    protected $fromParam = 'grid';

    protected function getState($param = null)
    {
        return State\DBProvider::getGridViewState($param);
    }
}
