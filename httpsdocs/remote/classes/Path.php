<?php
class Path{
	public static function getId($path = ''){
		$id = explode('/', $path);
		$id = array_pop($id);
		$id = is_numeric($id) ? $id : Browser::getRootFolderId();
		return $id;
	}
	public static function getPathText($p){
		$path = empty($p->path) ? '/' : $p->path;
		while($path[0] == '/') $path = substr($path, 1);
		$path = explode('/', $path);
		$ids = array_filter($path, 'is_numeric');
		$id = array_pop($ids);
		$res = mysqli_query_params('select f_get_tree_ids_path($1)', $id) or die(mysqli_query_error());
		if($r = $res->fetch_row()){
			$path = explode('/', $r[0]);
			array_shift($path);
			array_shift($path);
			$ids = $path;
		}
		$res->close();
		
		if(empty($path)) return '/';
		if($path[0] == Browser::getRootFolderId()) array_shift($path);
		if(empty($ids)) return '/';

		$names = array();
		$sql = 'select id, name from tree where id in ('.implode(',', $ids).')';
		$res = mysqli_query_params($sql ) or die(mysqli_query_error());
		while($r = $res->fetch_row()){
			$names[$r[0]] = $r[1];
		}
		$res->close();
		$rez = array();
		for ($i=0; $i < sizeof($path); $i++) { 
			if(isset($names[$path[$i]])){
				if((substr($names[$path[$i]], 0, 1) == '[') && (substr($names[$path[$i]], -1, 1) == ']') )
					$names[$path[$i]] = L(substr($names[$path[$i]], 1, strlen($names[$path[$i]]) -2));
				$rez[] = $names[$path[$i]];
			}else $rez[] = $path[$i]; 
		}
		/* exception for virtual folders when in cases folder */
		if( (sizeof($path) > 1) && (Path::getNodeSubtype($path[1]) == 4) ){
			if(sizeof($path) > 2){
				if(empty($path[2])) $rez[2] = L\OutOfOffice;
				else{
					$sql = 'select l'.UL_ID().' from tags where id = $1';
					$res = mysqli_query_params($sql, $path[2]) or die(mysqli_query_error());
					if($r = $res->fetch_row()) $rez[2] = $r[0];
					$res->close();
				}
			}
			if(sizeof($path) > 3) $rez[3] = empty($path[3]) ? strip_tags(L\noData) : $path[3];
			if(sizeof($path) > 4) $rez[4] = coalesce($path[4], strip_tags(L\noData));
		}
		return '/'.implode('/', $rez);
	}

	public static function getPathProperties($p){
		//getPathProperties
		$path = empty($p->path) ? '/' : $p->path;
		while($path[0] == '/') $path = substr($path, 1);
		$path = explode('/', $path);
		$ids = array_filter($path, 'is_numeric');
		if(empty($ids)){
			$ids = array(Browser::getRootFolderId());//return '/';
			$path = $ids;
		}
		// $props = array();
		$rez = array();
		$lastId = array_pop($ids);
		$sql = 'select id, name, `system`, `type`, subtype, f_get_tree_ids_path(id) `path`, f_get_objects_case_id($1) `case_id` from tree where id = $1'; //in ('.implode(',', $ids).')';
		$res = mysqli_query_params($sql, $lastId) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()) $rez = $r;
			// $props[$r['id']] = $r;
		$res->close();

		/* exception for virtual folders when in cases folder */
		// $rez = array( // suppose it's a virtual folder by default
		// 	'id' => null
		// 	,'system' => 1
		// 	,'type' => 0
		// );
		// if( (sizeof($path) <= 1) || (Path::getNodeSubtype($path[1]) != 4) || (sizeof($path) > 5))
		// 	$rez = $props[$path[sizeof($path)-1]];
		// if($props[$path[0]]['subtype'] == 2) $rez['inFavorites'] = true;
		// if($props[$path[0]]['type'] == 3){
		// 	$sql = 'select f_get_case_type_id($1)';
		// 	$res = mysqli_query_params($sql, $props[$path[0]]['id']) or die(mysqli_query_error());
		// 	if($r = $res->fetch_row()) $rez['case_type_id'] = $r[0];
		// 	$res->close();
		// }
		return $rez;
	}
	public static function getNodeSubtype($id){
		$rez = null;
		$sql = 'select `subtype` from tree where id = $1';
		$res = mysqli_query_params($sql, $id) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $rez = $r[0];
		$res->close();
		return $rez;
	}

}