<?php
/* 
	check and validate borrower's account
*/
session_start();
include_once('../config.php');
include_once('../includes/sip2.php');
include_once('../includes/json_encode.php');

$debug=0;

//if (!empty($_POST['barcode']) && (strlen($_POST['barcode'])==$patron_id_length OR empty($patron_id_length))){ //check that the barcode was posted and matches the length set in config.php 

	$mysip = new sip2;

	// Set host name
	$mysip->hostname = $sip_hostname;
	$mysip->port = $sip_port;
	
	// Identify a patron
//	$mysip->patron = $_POST['barcode'];
	
	// connect to SIP server
	$connect = $mysip->connect();
	
	if(!$connect){ //if the connection failed go to the out of order page
		echo json_encode('out of order');
		exit;
	}
	
	/*	if(!empty($sip_login)){
		$sc_login=$mysip->msgLogin($sip_login,$sip_password);
		$mysip->parseLoginResponse($mysip->get_message($sc_login));
	}
	*/


	$_SESSION['checkouts_this_session']=0;  ///////////////// ist das das einzige, was hier gemacht wird?
	
	session_write_close();
	
	//put include file into variable to dump as json back to the jquery script that initiated the call to this page
	ob_start();
	include_once( '../includes/welcome.php' );
	$response = ob_get_contents();
	ob_end_clean(); 
	echo json_encode($response);
	
	exit;

?>