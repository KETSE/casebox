<?php
namespace CB\DB;

use CB\CONFIG as CONFIG;

function connect($p = array())
{
    //check if not connected already
    if (!empty($GLOBALS['dbh'])) {
        return $GLOBALS['dbh'];
    }

    if (empty($p['db_host'])) {
        $p['db_host'] = CONFIG\DB_HOST;
    }
    if (empty($p['db_user'])) {
        $p['db_user'] = CONFIG\DB_USER;
    }
    if (empty($p['db_pass'])) {
        $p['db_pass'] = CONFIG\DB_PASS;
    }
    if (empty($p['db_name'])) {
        $p['db_name'] = '';//CONFIG\DB_NAME;
    }
    if (empty($p['db_port'])) {
        $p['db_port'] = CONFIG\DB_PORT;
    }

    $dbh = connectWithParams($p);

    return $dbh;
}

function connectWithParams($p)
{
    @$newParams = array(
        'host' => $p['db_host'],
        'user' => $p['db_user'],
        'pass' => $p['db_pass'],
        'name' => $p['db_name'],
        'port' => $p['db_port'],
        'initsql' => $p['initsql']
    );

    $dbh = null;
    $lastParams = array();
    if (!empty($GLOBALS['dbh'])) {
        $dbh = $GLOBALS['dbh'];
        $lastParams = $dbh->lastParams;
    }

    //check if new params are different from last params
    if ((@$lastParams['host'] != $newParams['host']) ||
        (@$lastParams['user'] != $newParams['user']) ||
        (@$lastParams['pass'] != $newParams['pass']) ||
        (@$lastParams['port'] != $newParams['port'])
    ) {
        //close previous connection
        if (!empty($dbh)) {
            $dbh->close();
        }

        // connect with new params
        try {
            $dbh = new \mysqli(
                $newParams['host'],
                $newParams['user'],
                $newParams['pass'],
                $newParams['name'],
                $newParams['port']
            );
        } catch (\Exception $e) {
            if (\mysqli_connect_errno()) {
                throw new \Exception('Unable to connect to DB: ' . \mysqli_connect_error());
                exit;
            }
        }
    }

    // if database changed then apply initsql if set
    if (@$lastParams['name'] != $newParams['name']) {
        $newParams['name'] = $dbh->real_escape_string($newParams['name']);
        $dbh->query('USE `'.$newParams['name'].'`');
        $dbh->query("SET NAMES 'UTF8'");
        if (!empty($newParams['initsql'])) {
            $dbh->query($newParams['initsql']);
        }
    }

    $dbh->lastParams = $newParams;

    $GLOBALS['dbh']  = $dbh;

    return $dbh;
}

if (!function_exists(__NAMESPACE__.'\dbQuery')) {
    function dbQueryCallback($at)
    {
        global $query__parameters;

        return $query__parameters[ $at[1]-1 ];
    }

    function dbQuery($query, $parameters = array(), $database = false)
    {
        if (!$database) {
            $database = $GLOBALS['dbh'];
        }

        // Escape parameters as required & build parameters for callback function
        global $query__parameters;

        if (!is_array($parameters)) {
            $parameters = array($parameters);
        }

        foreach ($parameters as $k => $v) {
            if (!is_scalar($v) && !is_null($v)) {
                throw new \Exception("param error: ".print_r($parameters, 1)."\n For SQL: $query", 1);
            }
            $parameters[$k] = is_int($v) ? $v : (
                null === $v ?
                'NULL' :
                "'".$database->real_escape_string($v)."'"
                );
        }

        $query__parameters = $parameters;

        // Call using mysqli_query
        $sql = preg_replace_callback('/\$([0-9]+)/', __NAMESPACE__.'\dbQueryCallback', $query);
        $GLOBALS['last_sql'] = $sql;

        return $database->query($sql);
    }
}

if (!function_exists(__NAMESPACE__.'\dbQueryError')) {
    function dbQueryError($dbh = false)
    {
        if (!\CB\isDebugHost()) {
            return 'Query error';
        }
        if (empty($dbh)) {
            $dbh = $GLOBALS['dbh'];
        }

        $rez = "\n\r<br /><hr />Query error: ".mysqli_error($dbh).
            "<hr /><br />\n\r";
        if (!empty($GLOBALS['last_sql']) && \CB\isDebugHost()) {
            $rez = "\n\r<br /><hr />Query: ".$GLOBALS['last_sql'].$rez;
        }
        throw new \Exception($rez);

        return $rez;
    }
}

if (!function_exists(__NAMESPACE__.'\startTransaction')) {
    function startTransaction()
    {
        return $GLOBALS['dbh']->autocommit(false);
    }
}

if (!function_exists(__NAMESPACE__.'\commitTransaction')) {
    function commitTransaction()
    {
        $GLOBALS['dbh']->commit();

        return $GLOBALS['dbh']->autocommit(true);
    }
}

if (!function_exists(__NAMESPACE__.'\dbLastInsertId')) {
    function dbLastInsertId()
    {
        return mysqli_insert_id($GLOBALS['dbh']);
    }
}

if (!function_exists(__NAMESPACE__.'\dbAffectedRows')) {
    function dbAffectedRows()
    {
        return mysqli_affected_rows($GLOBALS['dbh']);
    }
}

if (!function_exists(__NAMESPACE__.'\dbCleanConnection')) {
    function dbCleanConnection($dbh = false)
    {
        if (!$dbh) {
            $dbh = $GLOBALS['dbh'];
        }
        while (mysqli_more_results($dbh)) {
            if (mysqli_next_result($dbh)) {
                $result = mysqli_use_result($dbh);
                if (is_object($result)) {
                    mysql_free_result($result);
                }
            }
        }
    }
}
