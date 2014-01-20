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
	
	$myl=new ldap();
	$myl->hostname	= $ldap_hostname;
    $myl->port      = $ldap_port;
//    $myl->binddn 	= $ldap_binddn; // use internal binddn
    $myl->bindpw 	= $ldap_bindpw;
    $myl->searchbase	= $ldap_intsearchbase;  // look for internal user
    $myl->filter	= $ldap_filter;
	
if(!preg_match($patron_id_pattern,$_POST['barcode'])){ // not a patron code - try resolving a UID

	$_SESSION['cardUID']=$_POST['barcode'];
	
    // find in rz ldap first!
    //rebach special: a prepended '+' 
	$res=$myl->getcnfromuid('+'.$_POST['barcode']);  
	if(''==$res) // not found - try without '+'
		$res=$myl->getcnfromuid($_POST['barcode']);  
	
	if(''!=$res){
		$_SESSION['state']='done';
  		echo json_encode(array('state'=>'done','hint'=>'<p id="fin">Fertig! Mit dieser Karte finden Sie Ausdrucke der RZ-Kennung <em>'.$res.'</em>.</p>'));
		exit;		
	}else{ // no internal user found, try external
	    $myl->searchbase	= $ldap_searchbase;  // look for external user
		$res=$myl->getcnfromuid($_POST['barcode']);  
	    if(preg_match($patron_id_pattern,$res)){ // found an external user
			$patronBarcode=$res;
			$_SESSION['state']='done';
  			echo json_encode(array('state'=>'done','hint'=>'<p id="fin">Fertig! Mit dieser Karte finden Sie <br>Ausdrucke der Bibliothekskennung <em>'.$res.'</em>.</p>'));
			exit;
	    }
	}

}else{ // matches! we got a barcode (instead a card UID)
	if('init'==($_SESSION['state'])){ // user did not read the UID before reading the barcode->drawing the card will end the session
  		$_SESSION['state']='fail'; // remember to exit with the next entry
		echo json_encode(array('state'=>'fail',
  		'hint'=>'Bitte die Karte zuerst auf den RFID Leser legen!'));
  		exit;
	}
	if('UID'==$_SESSION['state']){ // UID read before, barcode now
		$patronBarcode=$_POST['barcode'];
		$_SESSION['barcode']=$patronBarcode;

		// check if the barcode can be found in RZ entries
		$_SESSION['rzuser']=barcode2rzid($_POST['barcode']);
		if("" != $_SESSION['rzuser']){ // this is a hsu member

			$storeanswer=storeresult($_SESSION['cardUID'],$_SESSION['rzuser'],$_SESSION['barcode']);
			
			if('OK'==substr($storeanswer,0,2)){ // OK: registrierung hat funktioniert, ERROR - eben nicht..	
				// TODO pre-fill the (internal) user ldap (as a cache) with the number
	    		$myl->searchbase = $ldap_intsearchbase;
	    		$myl->filter	= $ldap_filter;
	    		$myl->addcardtocn($_SESSION['rzuser'],$_SESSION['cardUID']); // cache to internal ldap
	    		
				echo json_encode(array('state'=>'done',
		  		'hint'=>'<p id="fin">Fertig! Mit dieser Karte finden Sie jetzt Ausdrucke der RZ-Kennung <em>'.$_SESSION['rzuser'].'</em>.</p>',
		  		'uid'=>$_SESSION['cardUID']));

			}else{
				$hint='Die Registrierung der RZ-Kennung <em>'.$_SESSION['rzuser'].'</em> ist derzeit nicht m&ouml;glich. '.$storeanswer.' Bitte versuchen Sie es sp&auml;ter erneut.';
				echo json_encode(array('state'=>'fail',
		  		'hint'=>$hint,
		  		'uid'=>$_SESSION['cardUID']));
			}
	  		exit;
			
		}else{	// external user: put barcode in ldap
	    $myl->searchbase = $ldap_searchbase;
		$myl->filter	= $ldap_filter;
		if($myl->addcardtocn($_SESSION['barcode'],$_SESSION['cardUID'])){ // register succeeded
	    
  		echo json_encode(array('state'=>'done',
  		'hint'=>'<p id="fin">Fertig! Diese Karte ist unter '.$_SESSION['barcode'].' registriert</p>',
  		'uid'=>$_SESSION['cardUID']));

  		storeresult($_SESSION['cardUID'],$_SESSION['barcode'],$_SESSION['barcode']);
  		
  		exit;
		}else{
		echo json_encode(array('state'=>'fail',
  		'hint'=>"Die Registrierung ist fehlgeschlagen.",
  		'uid'=>$_SESSION['cardUID']));
  		exit;
		}
		}
	}
} // barcode matches
}


if(!empty($patronBarcode)){ // filled - if we found anything
	
  echo '<p id="fin">Fertig! Diese Karte ist bereits registriert!<p>Verbundene Anmeldekennung:'.$patronBarcode.' </p>';
	exit;


} else { // non-0705 entry read, asuming UID
	$_SESSION['state']="UID";
//	if($debug)trigger_error("account_check: invalid account",E_USER_NOTICE);
  		echo json_encode(array('state'=>'UID',
  			'hint'=>'<img src="images/barcoded_card1_80.png"/> <p id="step2">Identifikation anhand des Barcodes. Stecken Sie dazu Ihre Karte wie gewohnt in den Leseschlitz rechts.</p>'));
		exit;
	
}

// local record of registered cards
// PLUS transfer to verwaltung
function storeresult($uid,$cn,$bar){

//	http_post_data($url,$data); // not installed?
	
	$ch = curl_init();
	$vresult=""; // stores OK or ERROR from verwaltung
	
	// last not least die verwaltung	
	if(($cn!=$bar)){ // fuer externe waeren cn und barcode gleich..
//https://debian.unibw-hamburg.de/CuaYc7t1dpSr/getrfid.php?bibcode=<bibcode>&rfid=<rfidcode>&user=<username>
		$vdata=array('bibcode'=>$bar,
			'rfid'=>$uid,
			'user'=>$cn);
		$vurl='https://debian.unibw-hamburg.de/CuaYc7t1dpSr/getrfid.php?bibcode='.$bar.'&rfid='.$uid.'&user='.$cn;
		$vurl='https://debian.unibw-hamburg.de/CuaYc7t1dpSr/getrfid.php';
		curl_setopt($ch, CURLOPT_URL, $vurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		//		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $vdata);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
		if(!$curlres=curl_exec($ch))
			error_log('empty curl update result: '.curl_error($ch));

		$vresult=$curlres;
//		error_log('stored with '.$vresult);
	}
	
	
	$url=	"http://bibweb1.ub.hsu-hh.de:5984/hsuhitag/";
//	$data=  json_encode(array('_id'=>$uid,'hitaguid'=>$uid,'cn'=>$cn));
	$data=  json_encode(array('hitaguid'=>$uid,'cn'=>$cn));

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url.$uid);

	$resjson=curl_exec($ch);
	$res=json_decode($resjson,true);

	$arequest = array('_id'=>$uid,'hitaguid'=>$uid,'cn'=>$cn,'bar'=>$bar);

	if(isset($res['_rev']))
		$arequest['_rev']=$res['_rev'];
		// strlen is 0 on NULL or empty string
	if(0 != strlen($vresult)) // record the result from previous transfer
		$arequest['stored']=$vresult;
		
	$data=  json_encode($arequest);

	curl_setopt($ch, CURLOPT_URL, $url);
//	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	$curlres=curl_exec($ch);

	
	curl_close($ch);

//	echo result from verwaltung
return $vresult;
}

// new: use ldap attribute for borrower_bar
function barcode2rzid($bar){
	global $myl,$ldap_intsearchbase,$ldap_intbarcode;

	$myl->searchbase	= $ldap_intsearchbase;  // look for internal user
    $myl->filter	= $ldap_intbarcode;		// search with barcode
	
	$rzuser=$myl->getcnfromuid($bar);  

	if(''!=$rzuser) return $rzuser;
	
	return null;
	return "pruefbituser";
	return $rzuser;
}

?>