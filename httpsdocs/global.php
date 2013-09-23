<?php

/**
 * global namespace
 */
function __autoload($className)
{
    // $arr = explode('\\', $className);
    // $className = array_pop($arr);
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
