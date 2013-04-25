<?php

namespace CB;

class Path{
	/* get last element di from a path or return root folder id if no int element is found */
	public static function getId($path = ''){
		$path = trim($path);
		while(!empty($path) && (substr($path, -1) == '/') ) $path = substr($path, 0, strlen($path)-1);
		// echo $path;
		$id = explode('/', $path);
		$id = array_pop($id);
		$id = is_numeric($id) ? $id : Browser::getRootFolderId();
		return $id;
	}
	
	public static function getPath($id){
		$rez = array('success' => false);
		if(!is_numeric($id)) return $rez;
		$sql = 'select f_get_tree_ids_path(case when `type` = 2 then target_id else id end) from tree where id = $1';
		$res = DB\mysqli_query_params($sql, $id) or die(DB\mysqli_query_error());
		if($r = $res->fetch_row())
			$rez = array('success' => true, 'path' => $r[0]);
		$res->close();
		return $rez;
	}
	
	public static function getPidPath($id){
		$rez = array('success' => false);
		if(!is_numeric($id)) return $rez;
		$sql = 'select f_get_tree_ids_path(pid) from tree where id = $1';
		$res = DB\mysqli_query_params($sql, $id) or die(DB\mysqli_query_error());
		if($r = $res->fetch_row())
			$rez = array('success' => true, 'id' => $id, 'path' => $r[0]);
		$res->close();
		return $rez;
	}

	public static function getPathText($p){
		$path = empty($p->path) ? '/' : $p->path;
		while($path[0] == '/') $path = substr($path, 1);
		$path = explode('/', $path);
		$ids = array_filter($path, 'is_numeric');
		$id = array_pop($ids);
		$res = DB\mysqli_query_params('select f_get_tree_ids_path($1)', $id) or die(DB\mysqli_query_error());
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
		$res = DB\mysqli_query_params($sql ) or die(DB\mysqli_query_error());
		while($r = $res->fetch_row()){
			$names[$r[0]] = $r[1];
		}
		$res->close();
		$rez = array();
		for ($i=0; $i < sizeof($path); $i++) { 
			if(isset($names[$path[$i]])){
				if((substr($names[$path[$i]], 0, 1) == '[') && (substr($names[$path[$i]], -1, 1) == ']') )
					$names[$path[$i]] = Util\coalesce(L\get(substr($names[$path[$i]], 1, strlen($names[$path[$i]]) -2)) , $names[$path[$i]]);
				$rez[] = $names[$path[$i]];
			}else $rez[] = $path[$i]; 
		}
		/* exception for virtual folders when in cases folder */
		if( (sizeof($path) > 1) && (Path::getNodeSubtype($path[1]) == 4) ){
			if(sizeof($path) > 2){
				if(empty($path[2])) $rez[2] = L\OutOfOffice;
				else{
					$sql = 'select l'.USER_LANGUAGE_INDEX.' from tags where id = $1';
					$res = DB\mysqli_query_params($sql, $path[2]) or die(DB\mysqli_query_error());
					if($r = $res->fetch_row()) $rez[2] = $r[0];
					$res->close();
				}
			}
			if(sizeof($path) > 3) $rez[3] = empty($path[3]) ? strip_tags(L\noData) : $path[3];
			if(sizeof($path) > 4) $rez[4] = Util\coalesce($path[4], strip_tags(L\noData));
		}
		return '/'.implode('/', $rez);
	}

	public static function getPathProperties($p){
		$path = empty($p->path) ? '/' : $p->path;
		while($path[0] == '/') $path = substr($path, 1);
		$path = explode('/', $path);
		$ids = array_filter($path, 'is_numeric');
		if(empty($ids)){
			$ids = array(Browser::getRootFolderId());//return '/';
			$path = $ids;
		}
		$rez = array();
		$lastId = array_pop($ids);
		$sql = 'select id, name, `system`, `type`, subtype, f_get_tree_ids_path(id) `path`, f_get_objects_case_id($1) `case_id`'.
			',(select template_id from objects where id = $1) `template_id` from tree where id = $1'; //in ('.implode(',', $ids).')';
		$res = DB\mysqli_query_params($sql, $lastId) or die(DB\mysqli_query_error());
		while($r = $res->fetch_assoc()) $rez = $r;
		$res->close();

		return $rez;
	}
	
	/* tree nodes can contain Translation variable in place of name like: [MyDocuments] */
	public static function replaceCustomNames($path){
		$path = explode('/', $path);
		for ($i=0; $i < sizeof($path); $i++)
			if( (substr($path[$i], 0, 1) == '[') && (substr($path[$i], -1) == ']') ){
				$l = substr($path[$i], 1, mb_strlen($path[$i])-2);
				$path[$i] = Util\coalesce( L\get( $l ), $l);
			}
		$path = implode('/', $path);
		return $path;
	}

	public static function getNodeSubtype($id){
		$rez = null;
		$sql = 'select `subtype` from tree where id = $1';
		$res = DB\mysqli_query_params($sql, $id) or die(DB\mysqli_query_error());
		if($r = $res->fetch_row()) $rez = $r[0];
		$res->close();
		return $rez;
	}

}