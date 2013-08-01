<?php
session_start();

require_once '/../lib/LiveApiClient.php';
require_once '/../config.inc.php';

$live = new LiveAPIClient(LIVE_ID, LIVE_SEC);

if(isset($_SESSION['livetoken']) && is_object(json_decode($_SESSION['livetoken']))){
	$live->setAccessToken($_SESSION['livetoken']);

	$res = $live->getContacts();

	echo '<h2>Contacts</h2>';
	echo '<pre>';
	print_r($res);
	echo '</pre>';
}
else{
	echo 'No valid token found. <a href="get_profile_example.php">Get one</a>';
}
?>