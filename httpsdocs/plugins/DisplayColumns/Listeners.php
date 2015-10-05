<?php
namespace DisplayColumns;

class Listeners
{

    public function onBeforeSolrQuery(&$p)
    {
        $ip = &$p['inputParams'];
        $class = null;

        if (!empty($ip['from']) && ($ip['from'] == 'tree')) {
            return;
        }

        switch (@$ip['view']['type']) {
            case 'grid':
                $class= new Grid();
                break;

            case 'stream':
            case 'activityStream':
                $class= new ActivityStream();
                break;

            case 'formEditor':
                $class= new FormEditor();
                break;

            case 'calendar':
                $class= new Calendar();
                unset($p['params']['sort']);
                break;

            case 'charts':
            case 'pivot':
                //unset sort params for other views
                //because other views (chart, calendar) dont need sorting
                //and would result in error if sorted by a custom column and not processed
                unset($p['params']['sort']);

                return;
                break;

            default:
                return;
        }

        return  $class->onBeforeSolrQuery($p);
    }

    public function onSolrQueryWarmUp(&$p)
    {
        $ip = &$p['inputParams'];
        $class = null;

        switch (@$ip['view']['type']) {
            case 'grid':
                $class= new Grid();
                break;

            case 'activityStream':
                $class= new ActivityStream();
                break;

            case 'formEditor':
                $class= new FormEditor();
                break;

            default:
                return;
        }

        return $class->onSolrQueryWarmUp($p);
    }

    public function onSolrQuery(&$p)
    {
        $ip = &$p['inputParams'];
        $class = null;

        if (!empty($ip['from']) && ($ip['from'] == 'tree')) {
            return;
        }

        switch (@$ip['view']['type']) {
            case 'grid':
                $class= new Grid();
                break;

            case 'activityStream':
                $class= new ActivityStream();
                break;

            case 'formEditor':
                $class= new FormEditor();
                break;

            case 'calendar':
                $class= new Calendar();
                break;

            default:
                return;
        }

        return $class->onSolrQuery($p);
    }
}
