<?php 

date_default_timezone_set('Asia/Colombo');

require_once '/lib/LiveApiClient.php';
require_once '/config.inc.php';

$live = new LiveAPIClient(LIVE_ID, LIVE_SEC, 'http://www.liveapi/');


if(!$live->fetchAccessToken() && !isset($_COOKIE['livetoken'])){
	$live->authorize();
}
elseif(is_object($live->fetchAccessToken())){
	setcookie('livetoken', $live->getAccessToken(true));
	echo 'connected to API<br>';
	echo 'token is ' . $live->getAccessToken(true) . '<br>';
	echo '<a href="' . $_SERVER['PHP_SELF'] . '">Click to Refresh</a>';
	//header('Location: '. $_SERVER['PHP_SELF']);
}
elseif(isset($_COOKIE['livetoken']) && is_object(json_decode($_COOKIE['livetoken']))){
	echo 'token was read from cookie<br>';
	echo 'token created at ' . date('Y m d - h i s') . '<br>';
	$live->setAccessToken($_COOKIE['livetoken']);
	
	$res = $live->getContacts();
	
	print_r($res);
}

?>