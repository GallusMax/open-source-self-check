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
$_POST['rzcn']='uhahn';
$rzcn=$_GET['q'];

//if (!empty($_POST['barcode']) && (strlen($_POST['barcode'])==$patron_id_length OR empty($patron_id_length))){ //check that the barcode was posted and matches the length set in config.php 

if(!empty($_GET['q'])){
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
	$myl->searchbase	= $ldap_intsearchbase;  // look for internal user
	$myl->filter	= $ldap_filter;


    $carlic_pattern='/\d{8,10}/';
    $rz_pattern='/[a-z]\w{2,}/';
    $myl->searchbase=$ldap_searchbase; // Library Users


    if(preg_match($patron_id_pattern,$rzcn)){ // 0705 patron barcodefound
        $mylt->filter="generationQualifier";
        $res=$mylt->search($rzcn);
        
        $myl->filter="cn";
        $myl->search($rzcn);
        
    }else if(preg_match($carlic_pattern,$rzcn)){ // cardnumber found - resolve to cn
        $myl->filter="carLicense";
        $myl->search($rzcn); // is a cardnumber here

        $mylt->filter='carLicense';
        $res=$mylt->search('+'.$rzcn);
        //$rescn=$mylt->getcnfromuid('+'.$rzcn);  
        if(!$mylt->getattr('cn')) // not found - try without '+'
            //$rescn=$mylt->getcnfromuid($rzcn);
            $res=$mylt->search($rzcn);
	
    }else // search for cn
        if(preg_match($rz_pattern,$rzcn)){ // only valid id (>2)
            $mylt->filter="cn"; 
            $res=$mylt->search($rzcn);
	}
	


    $res= new stdClass;
    $res->rz=new stdClass;
    $res->lbs=new stdClass;

	//    error_reporting(~E_ALL); // no warning on missing attrs

    $res->rz->cn=$mylt->getattr('cn');
    $res->rz->generationQualifier=$mylt->getattr('generationQualifier');
    $res->rz->employeeType=$mylt->getattr('employeeType');
    $res->rz->carLicense=$mylt->getattr('carLicense');
    $res->rz->givenName=$mylt->getattr('givenName');
    $res->rz->sn=$mylt->getattr('sn');
    $res->rz->mail=$mylt->getattr('mail');
    $res->rz->displayName=$mylt->getattr('displayName');

    $res->lbs->cn=$myl->getattr('cn');
    $res->lbs->carLicense=$myl->getattr('carLicense');
    $res->lbs->givenName=$myl->getattr('givenName');
    $res->lbs->displayName=$myl->getattr('displayName');
    $res->lbs->mail=$myl->getattr('mail');
    
    //    echo "hello";
    //echo json_encode($_SESSION);
    echo json_encode($res);
   
    exit;


    if(!preg_match($patron_id_pattern,$_POST['barcode'])){ // not a patron code - try resolving a UID

	$_SESSION['cardUID']=$_POST['barcode'];
    
    // find in rz ldap first!
    //rebach special: a prepended '+' 
	$res=$mylt->getcnfromuid('+'.$_POST['barcode']);  
	if(''==$res) // not found - try without '+'
		$res=$mylt->getcnfromuid($_POST['barcode']);  
	
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

            /*            
			$storeanswer=storeresult($_SESSION['cardUID'],$_SESSION['rzuser'],$_SESSION['barcode']);
			
			if('OK'==substr($storeanswer,0,2)){ // OK: registrierung hat funktioniert, ERROR - eben nicht..	
				// TODO pre-fill the (internal) user ldap (as a cache) with the number
	    		$myl->searchbase = $ldap_intsearchbase;
	    		$myl->filter	= $ldap_filter;
                //	    		$myl->addcardtocn($_SESSION['rzuser'],$_SESSION['cardUID']); // cache to internal ldap
	    		
				echo json_encode(array('state'=>'done',
		  		'hint'=>'<p id="fin">Fertig! Mit dieser Karte finden Sie jetzt Ausdrucke der RZ-Kennung <em>'.$_SESSION['rzuser'].'</em>.</p>',
		  		'uid'=>$_SESSION['cardUID']));

			}else{
				$hint='Die Registrierung der RZ-Kennung <em>'.$_SESSION['rzuser'].'</em> ist derzeit nicht m&ouml;glich. '.$storeanswer.' Das Identitymanagement der Uni in Raum 1130 hilft weiter!';
				echo json_encode(array('state'=>'fail',
		  		'hint'=>$hint,
		  		'uid'=>$_SESSION['cardUID']));
			}
	  		exit;
			*/
            
		}else{	// external user: put barcode in ldap
	    $myl->searchbase = $ldap_searchbase;
		$myl->filter	= $ldap_filter;
		$myl->binddn	= $ldap_writedn;
		$myl->bindpw	= $ldap_writepw;
        /*
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
        */

        
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
  			'hint'=>'<img src="images/barcoded_card1_80.png"/> <p id="step2">Identifikation anhand des Barcodes. Stecken Sie dazu Ihre Karte in den Leseschlitz rechts, Barcode nach hinten.</p>'));
		exit;
	
}

// local record of registered cards
// PLUS transfer to verwaltung
function storeresult($uid,$cn,$bar){
    }

// new: use ldap attribute for borrower_bar
function barcode2rzid($bar){
	global $mylt;
	
	$rzuser=$mylt->getcnfromuid($bar);  

	if(''!=$rzuser) return $rzuser;
	
	return null;
	return "pruefbituser";
	return $rzuser;
}

?>
