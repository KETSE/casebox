<?php

spl_autoload_register('__autoload', true, true);

/**
 * global namespace
 */
function __autoload($className)
{
    // require_once $className . '.php';
    $className = str_replace(
        array(
            '\\'
            ,'_'
        ),
        '/',
        $className
    ).'.php';
    require_once $className;
}
