<?php
namespace CB\DB;

use CB\CONFIG as CONFIG;

function connect($p = array())
{
    //check if not connected already
    if (!empty($GLOBALS['dbh'])) {
        return $GLOBALS['dbh'];
    }
    try {
        $host = empty($p['db_host']) ? CONFIG\DB_HOST: $p['db_host'];
        $user = empty($p['db_user']) ? CONFIG\DB_USER: $p['db_user'];
        $pass = empty($p['db_pass']) ? CONFIG\DB_PASS: $p['db_pass'];
        $db_name = empty($p['db_name']) ? CONFIG\DB_NAME: $p['db_name'];
        $port = empty($p['db_port']) ? CONFIG\DB_PORT: $p['db_port'];
        $dbh = new \mysqli($host, $user, $pass, $db_name, $port);
    } catch (\Exception $e) {
        $err = debug_backtrace();
    }

    if (mysqli_connect_errno()) {
        throw new \Exception('Unable to connect to DB: ' . mysqli_connect_error());
        exit;
    } else {
        $dbh->query("SET NAMES 'UTF8'");
        if (defined('CB\\CONFIG\\DB_INITSQL')) {
            $dbh->query(CONFIG\DB_INITSQL);
        }
        if (!empty($GLOBALS['dbh'])) {
            unset($GLOBALS['dbh']);
        }
        $GLOBALS['dbh'] = $dbh;
    }

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
