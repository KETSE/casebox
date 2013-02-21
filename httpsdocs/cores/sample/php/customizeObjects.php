<?php
class customizeObjects{

	public function getCustomInfo($p){
		$rez = array('success' => true, 'data' => 'remote customInfo');
		if(is_numeric($p)){
			$sql = 'select name from tree where id = $1';
			$res = mysqli_query_params($sql, array($p)) or die(mysqli_query_error());
			if($r = $res->fetch_assoc()) $rez['data'] .= ': '.$r['name'];
			$res->close();
		}
		return $rez;
	}
}
?>