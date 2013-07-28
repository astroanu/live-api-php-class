<?php 

require_once '/lib/LiveApiClient.php';
require_once '/config.inc.php';

$live = new LiveAPIClient(LIVE_ID, LIVE_SEC, 'http://www.liveapi/');


if(!$live->fetchAccessToken() && !isset($_COOKIE['livetoken'])){
	$live->authorize();
}
elseif(is_object($live->fetchAccessToken())){
	setcookie('livetoken', $live->getAccessToken(true));
	echo 'connected';
	header('Location: '. $_SERVER['PHP_SELF']);
}
elseif(isset($_COOKIE['livetoken']) && is_object(json_decode($_COOKIE['livetoken']))){
	echo 'token read from cookie';
	$live->setAccessToken($_COOKIE['livetoken']);
	$res = $live->request('https://apis.live.net/v5.0/me/contacts', 'GET');
	print_r($res);
}

?>