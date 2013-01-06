<?php
/* 
	ACS status check page (called from the out of order page)
*/

include_once('../config.php');
include_once('../includes/sip2.php');
include_once('../includes/json_encode.php');

$mysip = new sip2;

// Set host name
$mysip->hostname = $sip_hostname;
$mysip->port = $sip_port;

// connect to SIP server
$connect=$mysip->connect();

if ($connect) {
	echo json_encode('online');
}
?>