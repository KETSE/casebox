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

            default:
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
