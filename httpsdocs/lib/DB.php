<?php
function connect2DB(){
	$dbh = new mysqli(CB_get_param('db_host'),
		CB_get_param('db_user'),
		CB_get_param('db_pass'),
		CB_get_param('db_name'),
		(int)CB_get_param('db_port')
	);

	if (mysqli_connect_errno()) {
		throw new Exception('Unable to connect to DB: ' . mysqli_connect_error());
		exit;
	} else {
		$dbh->query("SET NAMES 'UTF8'");
		$initSQL = CB_get_param('db_initSQL');
		if (!empty($initSQL)) $dbh->query($initSQL);
		$_SESSION['dbh'] = $dbh;
	}
	return $dbh;
}

if( !function_exists( 'mysql_query_params' ) ) {
	function mysql_query_params__callback( $at ) {
		global $mysql_query_params__parameters;
		return $mysql_query_params__parameters[ $at[1]-1 ];
	}

	function mysql_query_params( $query, $parameters=array(), $database=false ) {
		// Escape parameters as required & build parameters for callback function
		global $mysql_query_params__parameters;
		foreach( $parameters as $k=>$v )
		$parameters[$k] = ( is_int( $v ) ? $v : ( NULL===$v ? 'NULL' : "'".mysql_real_escape_string( $v )."'" ) );
		$mysql_query_params__parameters = $parameters;

		// Call using mysql_query
		if( false===$database )
			return mysql_query( preg_replace_callback( '/\$([0-9]+)/', 'mysql_query_params__callback', $query ) );
		else return mysql_query( preg_replace_callback( '/\$([0-9]+)/', 'mysql_query_params__callback', $query ), $database );
	}
}

if( !function_exists( 'mysqli_query_params' ) ) {
	function mysqli_query_params__callback( $at ) {
		global $mysqli_query_params__parameters;
		return $mysqli_query_params__parameters[ $at[1]-1 ];
	}

	function mysqli_query_params( $query, $parameters=array(), $database=false ) {
		if(!$database) $database = $_SESSION['dbh'];
		// Escape parameters as required & build parameters for callback function
		global $mysqli_query_params__parameters;
		if(!is_array($parameters)) $parameters = array($parameters);
		foreach( $parameters as $k=>$v )
		$parameters[$k] = ( is_int( $v ) ? $v : ( NULL===$v ? 'NULL' : "'".$database->real_escape_string( $v )."'" ) );
		$mysqli_query_params__parameters = $parameters;

		// Call using mysqli_query
		$sql = preg_replace_callback( '/\$([0-9]+)/', 'mysqli_query_params__callback', $query );
		$_SESSION['last_sql'] = $sql;
		return $database->query( $sql );
	}
}

if( !function_exists( 'mysqli_query_error' ) ) {
	function mysqli_query_error($dbh = false){
		if(!is_debug_host()) return 'Error';
		if(!$dbh) $dbh = $_SESSION['dbh'];
		$rez = "\n\r<br /><hr />Query error: ".mysqli_error($dbh)."
		       <hr /><br />\n\r";
		if(!empty($_SESSION['last_sql']) && is_debug_host()) $rez = "\n\r<br /><hr />Query: ".$_SESSION['last_sql'].$rez;
		throw new Exception($rez);
		return $rez;
	}
}

if( !function_exists( 'last_insert_id' ) ) {
    function last_insert_id(){
	return mysqli_insert_id($_SESSION['dbh']);
    }
}
if( !function_exists( 'affected_rows' ) ) {
    function affected_rows(){
	return mysqli_affected_rows($_SESSION['dbh']);
    }
}

if( !function_exists( 'mysqli_clean_connection' ) ) {
	function mysqli_clean_connection($dbh = false){
		if(!$dbh) $dbh = $_SESSION['dbh'];
		while(mysqli_more_results($dbh)){
			if(mysqli_next_result($dbh)){
				$result = mysqli_use_result($dbh);
				if(is_object($result)) mysql_free_result($result);
			}
		}
	}
}
?>