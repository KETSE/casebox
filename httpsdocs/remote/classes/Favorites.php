<?php
class Favorites{

	public function create($p){
		$rez = array('succes' => true, 'data' => array());
		if(empty($p->data)) return $rez;
		mysqli_query_params('insert into favorites (user_id, object_id) values($1, $2) on duplicate key update object_id = $2', array($_SESSION['user']['id'], $p->data->id)) or die(mysqli_query_error());
		$sql = 'SELECT t.id, t.type, t.name, f_get_tree_path(t.id) `path` FROM favorites f JOIN tree t ON f.object_id = t.id WHERE f.user_id = $1 and object_id = $2';
		$res = mysqli_query_params($sql, array($_SESSION['user']['id'], $p->data->id) ) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()){
			//$r['id'] = intval($r['id']);
			$rez['data'][] = $r;
		}
		$res->close();
		return $rez;
	}
	public function read($p){
		$rez = array('succes' => true, 'data' => array());
		$sql = 'SELECT t.id, t.type, t.name, f_get_tree_path(t.id) `path` FROM favorites f JOIN tree t ON f.object_id = t.id WHERE f.user_id = $1';
		$res = mysqli_query_params($sql, $_SESSION['user']['id']) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()) $rez['data'][] = $r;
		$res->close();
		return $rez;
	}
	public function update($p){
		$rez = array('succes' => true, 'data' => array());
		return $rez;
		
	}
	public function destroy($p){
		$rez = array('succes' => true, 'data' => array());
		mysqli_query_params('delete from favorites where user_id = $1 and object_id = $2', array($_SESSION['user']['id'], intval($p->data) ) ) or die(mysqli_query_error());
		return $rez;
	}
}
?>