<?php
namespace CB\DB;
use CB\config as config;

function connect( $p = array() ){
	//check if not connected already
	if(!empty($GLOBALS['dbh'])) return $GLOBALS['dbh'];
	try {
		$host = empty($p['db_host']) ? config\db_host: $p['db_host'];
		$user = empty($p['db_user']) ? config\db_user: $p['db_user'];
		$pass = empty($p['db_pass']) ? config\db_pass: $p['db_pass'];
		$db_name = empty($p['db_name']) ? config\db_name: $p['db_name'];
		$port = empty($p['db_port']) ? config\db_port: $p['db_port'];
		$dbh = new \mysqli($host, $user, $pass, $db_name, $port);
	} catch (Exception $e) {
		$err = debug_backtrace();
		var_dump($err);
	}

	if (mysqli_connect_errno()) {
		throw new \Exception('Unable to connect to DB: ' . mysqli_connect_error());
		exit;
	} else {
		$dbh->query("SET NAMES 'UTF8'");
		if (defined('CB\\config\\db_initSQL')) $dbh->query(config\db_initSQL);
		if(!empty($GLOBALS['dbh'])) unset($GLOBALS['dbh']);
		$GLOBALS['dbh'] = $dbh;
	}
	return $dbh;
}

if( !function_exists( __NAMESPACE__.'\mysqli_query_params' ) ){
	function mysqli_query_params__callback( $at ) {
		global $mysqli_query_params__parameters;
		return $mysqli_query_params__parameters[ $at[1]-1 ];
	}

	function mysqli_query_params( $query, $parameters=array(), $database=false ){
		if(!$database) $database = $GLOBALS['dbh'];
		
		// Escape parameters as required & build parameters for callback function
		global $mysqli_query_params__parameters;
		
		if(!is_array($parameters)) $parameters = array($parameters);
		
		foreach( $parameters as $k=>$v )
			$parameters[$k] = ( is_int( $v ) ? $v : ( NULL===$v ? 'NULL' : "'".$database->real_escape_string( $v )."'" ) );
		
		$mysqli_query_params__parameters = $parameters;

		// Call using mysqli_query
		$sql = preg_replace_callback( '/\$([0-9]+)/', __NAMESPACE__.'\mysqli_query_params__callback', $query );
		$GLOBALS['last_sql'] = $sql;
		
		return $database->query( $sql );
	}
}

if( !function_exists( __NAMESPACE__.'\mysqli_query_error' ) ){
	function mysqli_query_error($dbh = false){
		if(!\CB\is_debug_host()) return 'Query error';
		if(!$dbh) $dbh = $GLOBALS['dbh'];
		$rez = "\n\r<br /><hr />Query error: ".mysqli_error($dbh)."
		       <hr /><br />\n\r";
		if(!empty($GLOBALS['last_sql']) && \CB\is_debug_host()) $rez = "\n\r<br /><hr />Query: ".$GLOBALS['last_sql'].$rez;
		throw new \Exception($rez);
		return $rez;
	}
}

if( !function_exists( __NAMESPACE__.'\last_insert_id' ) ) {
    function last_insert_id(){
	return mysqli_insert_id($GLOBALS['dbh']);
    }
}
if( !function_exists( __NAMESPACE__.'\affected_rows' ) ) {
    function affected_rows(){
	return mysqli_affected_rows($GLOBALS['dbh']);
    }
}

if( !function_exists( __NAMESPACE__.'\mysqli_clean_connection' ) ) {
	function mysqli_clean_connection($dbh = false){
		if(!$dbh) $dbh = $GLOBALS['dbh'];
		while(mysqli_more_results($dbh))
			if(mysqli_next_result($dbh)){
				$result = mysqli_use_result($dbh);
				if(is_object($result)) mysql_free_result($result);
			}
	}
}
?>