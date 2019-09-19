<?php
spl_autoload_register('__autoload', true, false);

/**
 * global namespace
 */
function __autoload($className)
{
    if (!class_exists($className)) {

        $className = str_replace(
            array(
                '\\'
                , '_'
            ),
            '/',
            $className
        ) . '.php';

        if (stream_resolve_include_path($className)) {
            require_once $className;
        }
    }
}
