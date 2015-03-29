<?php
namespace DisplayColumns;

class Listeners
{

    public function onBeforeSolrQuery(&$p)
    {
        $ip = &$p['inputParams'];
        $class = null;

        switch (@$ip['from']) {
            case 'grid':
                $class= new Grid();
                break;

            case 'formEditor':
                $class= new FormEditor();
                break;

            case 'tree':
                return;
                break;

            default:
                //unset sort params for other views
                //because other views (chart, calendar) dont need sorting
                //and would result in error if sorted by a custom column and not processed
                unset($p['params']['sort']);

                return;
        }

        return $class->onBeforeSolrQuery($p);
    }

    public function onSolrQuery(&$p)
    {
        $ip = &$p['inputParams'];
        $class = null;

        switch (@$ip['from']) {
            case 'grid':
                $class= new Grid();
                break;

            case 'formEditor':
                $class= new FormEditor();
                break;

            default:
                return;
        }

        return $class->onSolrQuery($p);
    }
}
