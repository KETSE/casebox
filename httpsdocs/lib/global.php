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

        if ( stream_resolve_include_path( $className ) ) {
            require_once $className;
        }
    }
}
