<?php
session_start();
include_once('../config.php');
include_once('../includes/queryfunction.php');

if ($use_mysql_logging && !empty($_SESSION['checkouts_this_session'])){ //should we load this cko session in the stats table in the database?
		
	$mysql_connection = mysql_pconnect($dbhostname, $dbusername, $dbpassword) or trigger_error(mysql_error(),E_USER_ERROR); 
	
	mysql_select_db($database, $mysql_connection);
	$find_last_month_year_entered=q(sprintf("select DATE_FORMAT(timestamp, '%%m-%%Y') from %s where location='%s' order by timestamp desc limit 0,1",
	mysql_real_escape_string($log_table_name),
	mysql_real_escape_string($sc_location)));
	
	if ($find_last_month_year_entered!=date('m-Y')){
	
		mysql_select_db($database, $mysql_connection);
		q(sprintf("insert into %s (count,sessions,timestamp,location) values (%s,1,now(),'%s')",
		mysql_real_escape_string($log_table_name),
		$_SESSION['checkouts_this_session'],
		mysql_real_escape_string($sc_location)));

	} else {

		mysql_select_db($database, $mysql_connection);
		q(sprintf("update %s set timestamp=now(), count=count+'%s', sessions=sessions+1 where location='%s' and DATE_FORMAT(timestamp, '%%m-%%Y')='%s'",
		mysql_real_escape_string($log_table_name),
		$_SESSION['checkouts_this_session'],
		mysql_real_escape_string($sc_location),
		mysql_real_escape_string($find_last_month_year_entered)));

	}
}
	
// kill the session
$_SESSION = array();
session_destroy();

//redirect
header("location:../index.php?page=home");
exit;
?>