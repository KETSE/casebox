<?php
namespace CB\TreeNode;

class TasksForCase extends Tasks
{
    protected function acceptedPath()
    {
        $p = &$this->path;
        if (empty($p)) {
            return false;
        }

        if ($this->lastNode instanceof Dbnode) {
            if (\CB\Objects::getType($this->lastNode->id) !== 'case') {
                return false;
            }
        } elseif (!($this->lastNode instanceof Dbnode)) {
            return false;
        }

        return true;

    }

    protected function createDefaultFilter()
    {
        parent::createDefaultFilter();

        //add case_id filter
        $node = &$this->lastNode;
        while (!($node instanceof Dbnode) && !empty($node->parent)) {
            $node = $node->parent;
        }

        if ($node instanceof Dbnode) {
            $this->fq[] = 'pids:'.$node->id;
        }
    }
}
