<?php
spl_autoload_register('__autoload', true, true);

/**
 * global namespace
 */
function __autoload($className)
{
    // require_once $className . '.php';

    if (!class_exists($className)) {

        $className = str_replace(
                array(
                '\\'
                , '_'
                ), '/', $className
            ).'.php';

        if (stream_resolve_include_path($className)) {
            require_once $className;
        } elseif (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.$className)) {
            require_once $className;
        } else {
           // echo dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.$className;
        }

    }
}
