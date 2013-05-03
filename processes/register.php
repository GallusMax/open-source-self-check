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

if (!empty($_POST['barcode']) && (strlen($_POST['barcode'])==$patron_id_length OR empty($patron_id_length))){ //check that the barcode was posted and matches the length set in config.php 

if(!preg_match($patron_id_pattern,$_POST['barcode'])){ // not a patron code - try resolving a UID

	$_SESSION['cardUID']=$_POST['barcode'];

	$myl=new ldap();
	$myl->hostname	= $ldap_hostname;
    $myl->port      = $ldap_port;
    $myl->binddn 	= $ldap_binddn;
    $myl->bindpw 	= $ldap_bindpw;
    $myl->searchbase	= $ldap_searchbase;
    $myl->filter	= $ldap_filter;

	$res=$myl->getcnfromuid($_POST['barcode']);
	
	// TODO check the RZ ldap, too
	
	if(preg_match($patron_id_pattern,$res)){ // found!!
		$patronBarcode=$res;
		$_SESSION['state']='done';
  		echo json_encode(array('state'=>'done','hint'=>'Fertig! Diese Karte ist bereits registriert und kann im Drucksystem unter der Kennung '.$patronBarcode.' genutzt werden'));
		exit;		
	};

}else{ // matches!
	if(!isset($_SESSION['state'])){ // user did not read the UID before reading the barcode->drawing the card will end the session
  		echo json_encode(array('state'=>'fail','hint'=>'Bitte die Karte zuerst auf den RFID Leser legen!'));
  		exit;
	}
	if('UID'==$_SESSION['state']){ // UID read before, barcode now
		$patronBarcode=$_POST['barcode'];
		$_SESSION['barcode']=$patronBarcode;
		//TODO if barcode in email2library -> change request to RZ
		
		
		
		// external user: put barcode in ldap
		$myl=new ldap();
		$myl->hostname	= $ldap_hostname;
	    $myl->port      = $ldap_port;
//	    $myl->binddn 	= $ldap_binddn; //use default  :-)
//	    $myl->bindpw 	= $ldap_bindpw;
	    $myl->searchbase	= $ldap_searchbase;
		
		if($myl->addcardtocn($_SESSION['barcode'],$_SESSION['cardUID'])){ // register succeeded
	    
  		echo json_encode(array('state'=>'done',
  		'hint'=>"Fertig! Diese Karte ist unter ".$_SESSION['barcode']." registriert",
  		'uid'=>$_SESSION['cardUID']));
  		exit;
		}else{
		echo json_encode(array('state'=>'fail',
  		'hint'=>"Die Registrierung ist fehlgeschlagen.",
  		'uid'=>$_SESSION['cardUID']));
  		exit;
		}
	}
} // barcode matches
}


if(!empty($patronBarcode)){ // filled - if we found anything
	
  echo "Fertig! Diese Karte ist bereits registriert und kann im Drucksystem unter der Kennung $patronBarcode genutzt werden";
	exit;


	
//		session_regenerate_id();
//		session_destroy();
//		echo json_encode('blocked account');
	
	
	
	//	if($debug)trigger_error("extract and format account information and assign to session variables",E_USER_NOTICE);
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

} else { // non-0705 entry read, asuming UID
	$_SESSION['state']="UID";
//	if($debug)trigger_error("account_check: invalid account",E_USER_NOTICE);
  		echo json_encode(array('state'=>'UID',
  			'hint'=>'Schritt 2: Identifikation anhand des Barcodes. Stecken Sie dazu Ihre Karte in den Leseschlitz rechts.'));
		exit;
	
}
?>