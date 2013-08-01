<?php 
session_start();

require_once '/../lib/LiveApiClient.php';
require_once '/../config.inc.php';

$live = new LiveAPIClient(LIVE_ID, LIVE_SEC, 'http://www.liveapi/examples/oauth_example.php');

// try to fetch access token or see if we already have an access token saved?
if(!$live->fetchAccessToken() && !isset($_SESSION['livetoken'])){
	//if not procees to authorization
	$live->authorize();
}
elseif(is_object($live->fetchAccessToken())){
	// we have an access token set
	$_SESSION['livetoken'] = $live->getAccessToken(true); //save it
	echo 'connected to API<br>';
	echo 'token is ' . $live->getAccessToken(true) . '<br>';
	echo '<a href="' . $_SERVER['PHP_SELF'] . '">Click to Refresh</a>';
	// you might wanna do a header redirection here, the refresh link is for testing purposes
	//header('Location: '. $_SERVER['PHP_SELF']);
}
elseif(isset($_SESSION['livetoken']) && is_object(json_decode($_SESSION['livetoken']))){
	echo 'token was read from cookie<br>';
	echo 'token created at ' . date('Y m d - h i s') . '<br>';
	$live->setAccessToken($_SESSION['livetoken']); // set token from session
	
	$res = $live->getContacts(); // get some data
	
	echo '<pre>';
	print_r($res);
	echo '</pre>';
}

?>