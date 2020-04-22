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
$_POST['rzcn']='12345';
$rzcn='';
if(isset($_POST['q']))$rzcn=$_POST['q'];

    $res= new stdClass;
    $res->rz=new stdClass;
    $res->rz->cn=false;
    $res->bib=new stdClass;

  error_reporting(~E_ALL); // no warning on missing attrs

if(!empty($rzcn)){ // skip on empty input
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
        $resok=$mylt->search($rzcn);
        
        $myl->filter="cn";
        $myl->search($rzcn);
        
    }else if(preg_match($carlic_pattern,$rzcn)){ // cardnumber found - resolve to cn
        $myl->filter="carLicense";
        $myl->search($rzcn); // is a cardnumber here

        $mylt->filter='carLicense';
        $resok=$mylt->search('+'.$rzcn);
        //$rescn=$mylt->getcnfromuid('+'.$rzcn);  
        if(!$mylt->getattr('cn')) // not found - try without '+'
            //$rescn=$mylt->getcnfromuid($rzcn);
            $resok=$mylt->search($rzcn);
	
    }else // search for cn
        if(preg_match($rz_pattern,$rzcn)){ // only valid id (>2)
            $mylt->filter="cn"; 
            $resok=$mylt->search($rzcn);
	}
	


    $res->rz->cn=$mylt->getattr('cn');
    $res->rz->generationQualifier=$mylt->getattr('generationQualifier');
    $res->rz->employeeType=$mylt->getattr('employeeType');
    $res->rz->carLicense=$mylt->getattr('carLicense');
    $res->rz->givenName=utf8_encode($mylt->getattr('givenName'));
    $res->rz->sn=utf8_encode($mylt->getattr('sn'));
    $res->rz->mail=$mylt->getattr('mail');
    $res->rz->displayName=utf8_encode($mylt->getattr('displayName'));

    $res->bib->cn=$myl->getattr('cn');
    $res->bib->carLicense=$myl->getattr('carLicense');
    $res->bib->givenName=utf8_encode($myl->getattr('givenName'));
    $res->bib->displayName=utf8_encode($myl->getattr('displayName'));
    $res->bib->mail=$myl->getattr('mail');
    
    //echo "after getattr ".var_dump($res->rz);
}
    //echo json_encode($_SESSION);
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
    //echo json_encode($res);
   
    exit;


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
