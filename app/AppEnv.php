<?php

/**
 * Class AppEnv
 */
class AppEnv
{
    /**
     * @param string $env
     * @return string
     */
    public static function getRequestEnvironment($env = null)
    {
        if (empty($env)) {
            $env = 'default';
        }

        if (!empty($_SERVER['REQUEST_URI'])) {
            preg_match("/\/(c)\/([^\/]*)/is", $_SERVER['REQUEST_URI'], $match);

            if (!empty($match[1]) && !empty($match[2])) {
                    if ($match[1] == 'c') {
                    $env = $match[2];
                }
            }
        }

        return $env;
    }
}
