<?php
/* 
	check and validate borrower's account
*/
session_start();
include_once('../config.php');
include_once('../includes/sip2.php');
include_once('../includes/ldap.php');
include_once('../includes/json_encode.php');

$debug=0;
$patronBarcode='';

$mylt=new ldap();
$mylt->hostname	= $ldap0_hostname;
$mylt->port     = $ldap0_port;
$mylt->binddn 	= $ldap0_binddn;
$mylt->bindpw 	= $ldap0_bindpw;
$mylt->searchbase	= $ldap0_searchbase;
$mylt->filter	= $ldap0_filter;

$myl=new ldap();
$myl->hostname	= $ldap_hostname;
$myl->port      = $ldap_port;
$myl->binddn 	= $ldap_binddn;
$myl->bindpw 	= $ldap_bindpw;
$myl->searchbase	= $ldap_searchbase;
$myl->filter	= $ldap_filter;


function getexternaluserbarcode($uid){
	$myl=$GLOBALS['myl'];
	$myl->searchbase=$GLOBALS['ldap_searchbase'];
	$l_res=$myl->getcnfromuid($uid);
	return $l_res;
}

// internal users may have a "+" prepended in the ldap entry of their UID
function getinternaluserbarcode($uid){
	$mylt=$GLOBALS['mylt'];
//	$mylt->searchbase=$GLOBALS['ldap0_searchbase'];
	$mylt->search($uid);
    return $mylt->getattr($GLOBALS['ldap0_intbarcode']);
}

if (!empty($_POST['barcode']) && (strlen($_POST['barcode'])==$patron_id_length OR empty($patron_id_length))){ //check that the barcode was posted and matches the length set in config.php 

if(!preg_match($patron_id_pattern,$_POST['barcode'])){ // not a patron code - try resolving a UID

	//rebach special - some like it with leading +	
	$res=getinternaluserbarcode('+'.$_POST['barcode']);
	if(preg_match($patron_id_pattern,$res)) // found an internal user (RZ name does not matter here)
		$patronBarcode=$res;
	
	$res=getinternaluserbarcode($_POST['barcode']);
	if(preg_match($patron_id_pattern,$res)) // found an internal user (RZ name does not matter here)
		$patronBarcode=$res;
		
	$res=getexternaluserbarcode($_POST['barcode']);
	if(preg_match($patron_id_pattern,$res)) // found an external user
		$patronBarcode=$res;
		
}else{ // matches!
	$patronBarcode=$_POST['barcode'];
}
}

if(!empty($patronBarcode)){ // filled - if we found anything
	$mysip = new sip2;

	// Set host name
	$mysip->hostname = $sip_hostname;
	$mysip->port = $sip_port;
	
	// Identify a patron
	$mysip->patron = $patronBarcode;
	
	// connect to SIP server
	$connect = $mysip->connect();
	
	if(!$connect){ //if the connection failed go to the out of order page
	if($debug)trigger_error("SIP failed, returning out of order, exiting.",E_USER_WARNING);
		echo json_encode('out of order');
		exit;
	}
	
	if(!empty($sip_login)){
		$sc_login=$mysip->msgLogin($sip_login,$sip_password);
		$mysip->parseLoginResponse($mysip->get_message($sc_login));
	}
	
	// Get patron info response
	//	$ptrnmsg = $mysip->msgPatronInformation('charged');
	$ptrnmsg = $mysip->msgPatronInformation('hold');

	// parse the raw response into an array
	$patron_info = $mysip->parsePatronInfoResponse($mysip->get_message($ptrnmsg));

	//	print_r($patron_info);
	//if($debug)trigger_error("patron_info: {$patron_info}",E_USER_NOTICE);

	$mysip->msgEndPatronSession();

	if (strpos($patron_info['fixed']['PatronStatus'],'Y')!== false OR (!empty($patron_info['variable']['BL'][0]) && $patron_info['variable']['BL'][0]!='Y')){ //blocked or non-existent account?
		session_regenerate_id();
		session_destroy();
	if($debug)trigger_error("patron nonexistent or blocked, exiting.",E_USER_NOTICE);
		echo json_encode('blocked account');
		exit;
	}
	
	// patron verified here
	if($debug)trigger_error("extract and format account information and assign to session variables",E_USER_NOTICE);
	$_SESSION['patron_barcode']=$patronBarcode; 
	//if($debug)trigger_error("patron barcode: {$_SESSION['patron_barcode']}",E_USER_NOTICE);
	
	$patron_name='';
	if (!empty($patron_info['variable']['AE'][0])){
		$patron_name=$patron_info['variable']['AE'][0]; //patron's unformatted name
		if (strpos($patron_name,',')!==false){
			$patron_last_name=substr($patron_name, 0, strpos($patron_name,',')); //last name
			$patron_first_name=substr($patron_name, strpos($patron_name,','),strlen($patron_name)); //first name
			$patron_name=$patron_first_name.' '.$patron_last_name;
		}
		$_SESSION['name']=trim(str_replace(',','',$patron_name)); //patron's formatted name
	} else {
		$_SESSION['name']='';
	}	
	
	if (!empty($patron_info['variable']['BE'][0])){
		$_SESSION['email']=trim($patron_info['variable']['BE'][0]); //patron's email
	} else {
		$_SESSION['email']='';
	}
	
	if (!empty($patron_info['fixed']['OverdueCount'])){
		$_SESSION['overdues']=$patron_info['fixed']['OverdueCount']; //overdues
	} else {
		$_SESSION['overdues']=0;
	}
	
	if (!empty($patron_info['fixed']['HoldCount'])){
		$_SESSION['available_holds']=$patron_info['fixed']['HoldCount']; //holds
	} else {
		$_SESSION['available_holds']=0;
	}
	
	if (!empty($patron_info['fixed']['ChargedCount'])){
		$_SESSION['checkouts']=$patron_info['fixed']['ChargedCount']; //checkouts
	} else {
		$_SESSION['checkouts']=0;
	}
	
	if (!empty($patron_info['variable']['BV'][0])){
		$_SESSION['fines']=$currency_symbol.trim($patron_info['variable']['BV'][0]); //fines
	} else {
		$_SESSION['fines']='';
	}
	
	$_SESSION['checkouts_this_session']=0;
	
	session_write_close();
	
//	if($debug)trigger_error("ob_start()",E_USER_NOTICE);
	
	//put include file into variable to dump as json back to the jquery script that initiated the call to this page
	ob_start();
	include_once( '../includes/welcome.php' );
	$response = ob_get_contents();
//	if($debug)trigger_error("ob_get_contents {$response}",E_USER_NOTICE);
	ob_end_clean(); 

//	if($debug)trigger_error(json_encode($response),E_USER_NOTICE);
	
	echo json_encode($response);
	exit;

} else {
//       syslog(LOG_WARNING, "account_check: invalid account (patron empty)");
	if($debug)trigger_error("account_check: invalid account (patronBarcode: $patronBarcode)",E_USER_WARNING);
	echo json_encode('invalid account');
	exit;
	
}
?>