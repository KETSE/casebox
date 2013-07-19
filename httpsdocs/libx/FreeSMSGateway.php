<?php
class FreeSMSGateway{
	public static function sendSMS( $p ){
		$url = 'http://www.FreeSMSGateway.com/api_send';
		$post_contacts = array( $p['phone'] );
		$json_contacts = json_encode($post_contacts);

		$fields = array(
			'access_token' => '1dba8b9a5c87ef2a50ae887689d278ae'
			,'message' => urlencode( $p['message'] )
			,'send_to' => 'post_contacts'
			,'post_contacts' => urlencode($json_contacts)
		);
		$fields_string = array();
		foreach($fields as $k => $v) 
			$fields_string[] = $k.'='.$v;
		$fields_string = join('&', $fields_string);

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, count($fields) );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		//echo "$fields_string\n";var_dump($fields);
		$rez = curl_exec($ch);

		curl_close($ch);

		return $rez;
	}
}