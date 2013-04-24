<?php

namespace CB;

class Thesauri{

	function create($p){
		return Array('success' => true, 'data' => $p);
	}

	function read($p){
		$params = ($p && !empty($p->thesauriId)) ? ' and pid = '.intval($p->thesauriId) : '';
		$sql = 'SELECT t.id, t.pid, t.l'.USER_LANGUAGE_INDEX.' `name`, t.`order`, t.iconCls FROM tags t WHERE t.hidden IS NULL '.$params.'  ORDER BY pid, `order`, 3';
		$data = Array();
		$res = DB\mysqli_query_params($sql) or die(DB\mysqli_query_error());
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