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

		//TODO find in rz ldap first!
	
	
	$myl=new ldap();
	$myl->hostname	= $ldap_hostname;
    $myl->port      = $ldap_port;
    $myl->binddn 	= $ldap_binddn;
    $myl->bindpw 	= $ldap_bindpw;
    $myl->searchbase	= $ldap_searchbase;
    $myl->filter	= $ldap_filter;

	$res=$myl->getcnfromuid($_POST['barcode']);
	
	
	
	if(preg_match($patron_id_pattern,$res)){ // found!!
		$patronBarcode=$res;
		$_SESSION['state']='done';
  		echo json_encode(array('state'=>'done','hint'=>'Fertig! Diese Karte ist bereits registriert.<p>Anmeldekennung: '.$patronBarcode.'.'));
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

		// TODO check if the barcode has an entry in RZ db
		$_SESSION['rzuser']=barcode2rzid($_POST['barcode']);
		if("" != $_SESSION['rzuser']){ // this is a hsu member

			$storeanswer=storeresult($_SESSION['cardUID'],$_SESSION['rzuser'],$_SESSION['barcode']);
			
			if('OK'==substr($storeanswer,0,2)){ // OK: registrierung hat funktioniert, ERROR - eben nicht..	
				echo json_encode(array('state'=>'done',
		  		'hint'=>"Die Registrierung unter der RZ Kennung ".$_SESSION['rzuser']." wird an das Rechenzentrum weitergeleitet. ",
		  		'uid'=>$_SESSION['cardUID']));
			}else{
				$hint='Die Registrierung der RZ-Kennung <em>'.$_SESSION['rzuser'].'</em> ist derzeit nicht m&ouml;glich. '.$storeanswer.' Bitte versuchen Sie es sp&auml;ter erneut.';
				echo json_encode(array('state'=>'fail',
		  		'hint'=>$hint,
		  		'uid'=>$_SESSION['cardUID']));
			}			
	  		exit;
			
		}else{	// external user: put barcode in ldap
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
	
  echo "Fertig! Diese Karte ist bereits registriert!<p>Verbundene Anmeldekennung: $patronBarcode ";
	exit;


} else { // non-0705 entry read, asuming UID
	$_SESSION['state']="UID";
//	if($debug)trigger_error("account_check: invalid account",E_USER_NOTICE);
  		echo json_encode(array('state'=>'UID',
  			'hint'=>'Schritt 2: Identifikation anhand des Barcodes. Stecken Sie dazu Ihre Karte wie gewohnt in den Leseschlitz rechts.'));
		exit;
	
}

// local record of registered cards
// PLUS transfer to verwaltung
function storeresult($uid,$cn,$bar){

//	http_post_data($url,$data); // not installed?
	
	$ch = curl_init();
	
	$url=	"http://localhost:5984/hsuhitag/";
//	$data=  json_encode(array('_id'=>$uid,'hitaguid'=>$uid,'cn'=>$cn));
	$data=  json_encode(array('hitaguid'=>$uid,'cn'=>$cn));

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url.$uid);

	$resjson=curl_exec($ch);
	$res=json_decode($resjson,true);

	if(isset($res['_rev']))
		$data=  json_encode(array('_id'=>$uid,'hitaguid'=>$uid,'_rev'=>$res['_rev'],'cn'=>$cn,'bar'=>$bar));
	else
		$data=  json_encode(array('_id'=>$uid,'hitaguid'=>$uid,'cn'=>$cn,'bar'=>$bar));
	
//	echo $rev;
	

	curl_setopt($ch, CURLOPT_URL, $url);
//	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	$curlres=curl_exec($ch);

	
// last not least die verwaltung	
	if($cn!=$bar){ // fuer externe waeren cn und barcode gleich..
//https://debian.unibw-hamburg.de/CuaYc7t1dpSr/getrfid.php?bibcode=<bibcode>&rfid=<rfidcode>&user=<username>
		$vdata=array('bibcode'=>$bar,
			'rfid'=>$uid,
			'user'=>$cn);
		$vurl='https://debian.unibw-hamburg.de/CuaYc7t1dpSr/getrfid.php?bibcode='.$bar.'&rfid='.$uid.'&user='.$cn;
		$vurl='https://debian.unibw-hamburg.de/CuaYc7t1dpSr/getrfid.php';
		curl_setopt($ch, CURLOPT_URL, $vurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $vdata);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
		if(!$curlres=curl_exec($ch))
			echo 'curl update failed: '.curl_error($ch);
	}
	
	
	curl_close($ch);

//	echo $curlres;
return $curlres;
}

function barcode2rzid($bar){
	global $rzuser_host,$rzuser_db,$rzuser_user,$rzuser_pass;
	
	$rzdb=mysql_connect($rzuser_host,$rzuser_user,$rzuser_pass);
	if(!$rzdb) return null;
	
	if(!mysql_select_db($rzuser_db)) return null;
	
	$query = sprintf("select * from library2rzuser where library_number='%s'",mysql_real_escape_string($bar));
	
	$result=mysql_query($query);
	if(!$result) return mysql_error(); // nichts gefunden

	while ($row = mysql_fetch_assoc($result)) {
    	$rzuser= $row['rzuser'];
		return $rzuser;
	}
	return null;
	return "pruefbituser";
	return $rzuser;
}
?>