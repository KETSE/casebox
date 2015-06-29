<?php
namespace CB\TreeNode;

class TasksForCase extends Tasks
{
    /**
     * check if current class is configured to return any result for
     * given path and request params
     * @param  array   &$pathArray
     * @param  array   &$requestParams
     * @return boolean
     */
    protected function acceptedPath(&$pathArray, &$requestParams)
    {
        $lastNode = null;

        if (empty($pathArray)) {
            return false;
        } else {
            $lastNode = $pathArray[sizeof($pathArray) - 1];
        }

        if ($lastNode instanceof Dbnode) {
            if (\CB\Objects::getType($lastNode->id) !== 'case') {
                return false;
            }
        } elseif (get_class($lastNode) != get_class($this)) {
            return false;
        }

        return true;
    }

    protected function createDefaultFilter()
    {
        parent::createDefaultFilter();
        //add case_id filter
        $node = $this->lastNode;
        while (!($node instanceof Dbnode) && !empty($node->parent)) {
            $node = $node->parent;
        }

        if ($node instanceof Dbnode) {
            $this->fq[] = 'pids:'.$node->id;
        }
    }
}
