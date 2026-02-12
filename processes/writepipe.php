<?php
/* 
   read the uid from an authenticated POST
   and feed it into the corresponding pipe
*/

include_once('../config.php');

$loc = "pipe42";
if(isset($_POST['loc']))
	$loc=$_POST['loc'];

$thepipe=$pipepath."/".$loc;

if(isset($_POST['uid']))
	$uid=$_POST['uid'];

else{
	print "ERR no uid received - exiting";
	exit(1);
	}

if(!preg_match($patron_id_pattern,$uid)){ // not a patron code - (we read it from SSO?!)
     print "ERR no patron barcode: *".$uid."*";
     print " - exiting";
     exit(1);
     }

if(file_exists($thepipe)){ // dont open a new file(!) on my own  
$respipe=fopen($thepipe, "w");
if($respipe){
	stream_set_blocking($respipe, false); // user waits for it
		   fwrite($respipe,$uid);
		   fflush($respipe);
		   fclose($respipe);
print "<div class='ok_button button'><h1>Weiterleitung an den Verbucher..</h1></div>";

}else print "ERR could not open pipe ".$loc;
}else print "<div class='cancel_button button'><h1>Link abgelaufen.</h1></div>";
?>