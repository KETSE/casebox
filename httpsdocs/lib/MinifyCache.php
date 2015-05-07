<?php
namespace CB;

require_once(Config::get('MINIFY_PATH') . 'utils.php');

/**
 * function to load minify chached uris from file and set them in memory
 * @return void
 */
function loadMinifyUris()
{
    /*init Minify urls cache*/
    $cacheFile = MINIFY_CACHE_DIR . 'urls_cache';

    if (file_exists($cacheFile)) {
        $uris = json_decode(file_get_contents($cacheFile), true);
        Cache::set('MinifyUris', $uris);
    }
}

/**
 * function to load minify chached uris from file and set them in memory
 * @return void
 */
function saveMinifyUris()
{
    $uris = Cache::get('MinifyUris', []);

    if (!empty($uris['modified'])) {
        $cacheFile = MINIFY_CACHE_DIR . 'urls_cache';

        file_put_contents($cacheFile, json_encode($uris));
    }
}

/**
 * function to get uri for a group name from cache or generate it
 * @param  varchar $name
 * @return varchar
 */
function getMinifyGroupUrl($name)
{
    $uris = Cache::get('MinifyUris', []);

    if (IS_DEBUG_HOST || empty($uris[$name])) {
        $uris[$name] = Minify_getUri($name);
        $uris['modified'] = true;
    }

    Cache::set('MinifyUris', $uris);

    return $uris[$name];
}
