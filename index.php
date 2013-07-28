<?php 

require_once '/lib/LiveApiClient.php';
require_once '/config.inc.php';



$live = new LiveAPIClient(LIVE_ID, LIVE_SEC, 'http://www.liveapi/');

if(!$live->fetchAccessToken()){
	$live->authorize();
}else{
	//print_r($live->fetchAccessToken());
	
	$res = $live->request('https://apis.live.net/v5.0/me/contactsa', 'GET');
	print_r($res);
}

//print_r($live->getAccessToken());
//echo $_GET['code'];


?>