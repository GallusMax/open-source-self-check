<?php
/* 
   read the uid from a client-specific pipe
   and feed it back to the login call
*/

// set_time_limit(20); // has no effect on blocked read()

include_once('../config.php');

$thepipe=$pipepath."/".$pipe[$_SERVER['REMOTE_ADDR']];

if(isset($_GET['loc'])) $thepipe=$pipepath."/".$_GET['loc'];

//echo  $thepipe;
posix_mkfifo($thepipe,0644);

readfile($thepipe); // it could be so easy

unlink($thepipe); // prevent further use

?>