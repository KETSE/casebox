<?php
namespace DisplayColumns;

class Listeners
{

    public function onBeforeSolrQuery(&$p)
    {
        $ip = &$p['inputParams'];

        if (!empty($ip['from']) && ($ip['from'] == 'tree')) {
            return;
        }

        $className = empty($ip['view']['type'])
            ? ''
            : $ip['view']['type'];

        switch ($className) {
            case 'grid':
            case 'activityStream':
            case 'map':
            case 'formEditor':
                break;

            case 'stream':
                $className = 'activityStream';
                break;

            case 'calendar':
                unset($p['params']['sort']);
                break;

            case 'charts':
            case 'pivot':
                //unset sort params for other views
                //because other views (chart, calendar) dont need sorting
                //and would result in error if sorted by a custom column and not processed
                $p['rows'] = 0;
                unset($p['params']['sort']);

                return;
                break;

            default:
                return;
        }

        $className = __NAMESPACE__ . '\\' . ucfirst($className);
        $class = new $className;

        return  $class->onBeforeSolrQuery($p);
    }

    public function onSolrQueryWarmUp(&$p)
    {
        $ip = &$p['inputParams'];
        $className = empty($ip['view']['type'])
            ? ''
            : $ip['view']['type'];

        switch ($className) {
            case 'grid':
            case 'activityStream':
            case 'formEditor':
                break;

            // dont need to warm up anything, cause location field and title is extracted from solr
            // case 'map':

            default:
                return;
        }

        $className = __NAMESPACE__ . '\\' . ucfirst($className);
        $class = new $className;

        return $class->onSolrQueryWarmUp($p);
    }

    public function onSolrQuery(&$p)
    {
        $ip = &$p['inputParams'];

        if (!empty($ip['from']) && ($ip['from'] == 'tree')) {
            return;
        }

        $className = empty($ip['view']['type'])
            ? ''
            : $ip['view']['type'];

        switch ($className) {
            case 'grid':
            case 'activityStream':
            case 'map':
            case 'formEditor':
            case 'calendar':
                break;

            default:
                return;
        }

        $className = __NAMESPACE__ . '\\' . ucfirst($className);
        $class = new $className;

        return $class->onSolrQuery($p);
    }
}
