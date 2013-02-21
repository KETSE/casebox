<?php
class Thesauri{

	function create($p){
		return Array('success' => true, 'data' => $p);
	}

	function read($p){
		$params = ($p && !empty($p->thesauriId)) ? ' and pid = '.intval($p->thesauriId) : '';
		$sql = 'SELECT t.id, t.pid, t.l'.UL_ID().' `name`, t.`order`, t.iconCls FROM tags t WHERE t.hidden IS NULL '.$params.'  ORDER BY pid, `order`, 3';
		$data = Array();
		$res = mysqli_query_params($sql) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()) $data[] = $r;
		$res->close();
		return Array('success' => true, 'data' => $data);
	}

	function update($p){
		return Array('success' => true, 'data' => $p);
	}

	function destroy($p){
		return Array('success' => true, 'data' => $p);
	}
}
?>