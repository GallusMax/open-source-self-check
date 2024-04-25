<?php
// external auth for lbs linux ab 2.13.7
// credentials delivered as http basic auth
// answered as application/json

#session_start();
include_once('config_local.php');
include_once('includes/ldap.php');

         header('Content-Type: application/json; charset=utf-8');

$headersgot=getallheaders();

if(0 != strcmp($sharedsecret, $headersgot["Client-Authorization"])){ ## shared secret not sent: unauthorized client?
     reject();
     syslog(LOG_WARN,"lbsAuth: unauthorized client");
     exit ;
}

$Bauth=explode(" ",$headersgot["Authorization"]);
## TODO test auf "Basic"
if(0 != strcmp("Basic",$Bauth[0])){ # expected "Basic" missing
     reject();
     syslog(LOG_WARN,"lbsAuth: expected Authorization Basic missing");
     exit ;
}

$s_userPass=base64_decode($Bauth[1]);
$a_userPass=explode(":",$s_userPass,2);

if(2 == count($a_userPass)){ # user AND password are sent
#     syslog(LOG_DEBUG,"lbsAuth: user".$a_userPass[0]." pass".$a_userPass[1]);
     syslog(LOG_DEBUG,"lbsAuth: user".$a_userPass[0]." passXXX");
     userbind($a_userPass[0],$a_userPass[1]);
}else{
     syslog(LOG_WARN,"lbsAuth: Authorization incomplete - no password found after colon");
}

#dumpheaders();
#reject();
#accept("07053046192");


function userbind($user, $pass){
if(isrz($user)){ # look up lbsuid from ldap
$userbinddn="cn=$user,ou=Users,ou=HSU HH,dc=trust,dc=unibw-hamburg,dc=de";
$mylt=new ldap();
	  global $ldap0_hostname;
syslog(LOG_DEBUG,"ldap0_hostname ".$ldap0_hostname);
 	$mylt->hostname	= $ldap0_hostname;
	  global $ldap0_port;
	$mylt->port     = $ldap0_port;
	$mylt->binddn 	= $userbinddn;
	$mylt->bindpw 	= $pass;
	  global $ldap0_searchbase;
	$mylt->searchbase	= $ldap0_searchbase;
	  global $ldap0_filter;
	$mylt->filter	= $ldap0_filter;

$lbsuid=$mylt->getuidfromcn($user);

if(''!=$lbsuid){
     syslog(LOG_DEBUG,"lbsAuth: user".$user." accepting uid".$lbsuid);
	accept($lbsuid);
}else{
     syslog(LOG_DEBUG,"lbsAuth: user".$user." rejecting empty uid");
	reject();
}
}


if(islbs($user)){ # lbsuid equals $user
global $ldap_searchbase;
$userbinddn="cn=$user,".$ldap_searchbase;
$mylt=new ldap();
	  global $ldap_hostname;
syslog(LOG_DEBUG,"ldap_hostname ".$ldap_hostname);
	$mylt->hostname	= $ldap_hostname;
	  global $ldap_port;
	$mylt->port     = $ldap_port;
	$mylt->binddn 	= $userbinddn;
	$mylt->bindpw 	= $pass;
	  global $ldap_searchbase;
	$mylt->searchbase	= $ldap_searchbase;
	  global $ldap_filter;
	$mylt->filter	= $ldap_filter;

$lbsuid=$mylt->getcnfromuid($user);

if(''!=$lbsuid){
     syslog(LOG_DEBUG,"lbsAuth: user".$user." accepting uid".$lbsuid);
	accept($lbsuid);
}else{
     syslog(LOG_DEBUG,"lbsAuth: user".$user." rejecting empty uid");
	reject();
}
}

syslog(LOG_DEBUG,"lbsAuth: user".$user." unchecked, rejecting not_found");
reject();
}


function islbs($user){
$lbs_pattern='/0705\d{6}[\dxX]/';
return preg_match($lbs_pattern,$user);
}

function isrz($user){
$rz_pattern='/[a-z]\w{2,}/';
return preg_match($rz_pattern,$user);
}


function reject(){
         http_response_code(401);
         print '{ "code": "not_found","error": "credentials do not match" }'; # "not_found" allows for subsequent search in LBS
#         print '{ "code": "invalid_credentials","error": "credentials do not match" }';
	 exit;
}

function accept($borrowercode){
         http_response_code(200);
         print '{ "patron": "'.$borrowercode.'" }';
	 exit;
}

function dumpheaders(){
         $headersgot=getallheaders();
         $headerjson=json_encode($headersgot);
         syslog(LOG_DEBUG,$headerjson);

#        print $headerjson;

}


?>