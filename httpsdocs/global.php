<?php

/**
 * global namespace
 */
function __autoload($className)
{
    // $arr = explode('\\', $className);
    // $className = array_pop($arr);
    // require_once $className . '.php';
    require_once str_replace(
        array(
            '\\'
            ,'_'
        ),
        '/',
        $className
    ).'.php';
}
