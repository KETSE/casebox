<?php
namespace CB;

class Cache extends Singleton
{
    /**
     * check if a name is defined in cache
     * @param  varchar $name
     * @return boolean
     */
    public static function exist($name)
    {
        return isset(static::getInstance()->{$name});
    }

    /**
     * set a variable value into the cache
     * @param varchar $name name of variable
     * @param  $value
     */
    public static function set($name, $value)
    {
        static::getInstance()->{$name} = $value;
    }

    /**
     * get a variable value from the cache
     * @param varchar $name name of variable
     * @param  $value
     */
    public static function get($name)
    {
        if (static::exist($name)) {
            return static::getInstance()->{$name};
        }

        return null;
    }
}
