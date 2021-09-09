<?php
mb_internal_encoding("UTF-8");
setlocale(LC_TIME, "NL_nl");

// Connect to the database
define('AppEventsStats', true);
require_once('db.php');
$db = db_connect();

function log_ip($event, $ip, $acc){
	global $db;

	$sql = (strstr($ip, ':'))? 
		"insert into log ( event, src, src6, acc ) values ( '$event', 0, INET6_ATON('$ip'), '$acc' )" :
		"insert into log ( event, src, acc ) values ( '$event', INET_ATON('$ip'), '$acc' )";
	$db->query($sql);
}

function isAllowed($ip){
	// add any public/satic IP addresses here that should be allowed access 
	static $ips = array(
		"127.0.0.0",
		"::1"
	);

	if(in_array($ip, $ips, TRUE))
		return $ip;

	// log the request and return null if NOT allowed
	log_ip('XIP',$ip,4);
	return NULL;
}
?>
