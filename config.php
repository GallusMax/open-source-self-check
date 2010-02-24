<?php
/**
* 	Self Check
*
* 	This application provides a method of checking out and renewing materials via a web interface using 3M's SIP2 standard
* 	Thanks to John Wohlers for his sip2 class -this application would not have come about without it
*
* 	This thing is in its infancy having been in use for just a few weeks (as of Feb 6, 2010) and only 
*	tested on one library system (SirsiDynix Symphony) I know just enough of SIP2 to get it working on our system. 
*	The limitations of my knowldge will surely become apparant if others attempt to implement it on other 
*	systems with differing SIP2 configurations. In short, this self-check is very beta. 
*	In its current state it will surely not suit everyone's needs. I'm making it public at the request 
*	of several libraries and I'm hoping others find flaws and limitations and make improvements. 
*	If you do make additions or other customizations that improve or extend the self check's 
*	functionality I would love to hear about them.
*
*	@author     	Eric Melton <ericmelton@gmail.com>
* 	@licence    	http://opensource.org/licenses/gpl-3.0.html
* 	@copyright  	Eric Melton <ericmelton@gmail.com>
*	@version    	1.0
*/

//========================== SIP2 =================================
$sip_hostname = 'localhost';
$sip_port = "6002"; //yours might be 6001
$sip_login=''; 	//if your SIP2 server does not require a username and password leave these empty
$sip_password='';


//========================== Site Rules ==============================
$allow_manual_userid_entry=true;
$show_fines=true;
$show_available_holds=true;
$allow_email_receipts=false;
$display_php_errors='off'; //off or on
$hide_cursor_pointer=false; //hides default cursor pointer -should probably set to true on live self check


//========================== Logging =================================
/*	
	use the query below to setup the mysql table (if you change the table name set 
	the variable $log_table_name below equal to that new table name)
	
	CREATE TABLE IF NOT EXISTS `self_check_stats` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `count` int(11) NOT NULL DEFAULT '0',
	  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  KEY `date` (`count`)
	)  ;
*/
//====================================================================
$use_mysql_logging=false;	/* log your selfcheck checkout count by month? 
							use the query above to set up the table */
$log_table_name='self_check_stats';

//mysql connection info (ignore this if you're not using mysql logging)
$dbhostname = "localhost:3306";
$database = "";
$dbusername = "";
$dbpassword = "";

//====================== SIP2 Responses  ==============
/*
	GET YOUR SYSTEM'S RESPONSE MESSAGES BY ENTERING YOUR SIP2 CONNECTION INFO ABOVE THEN OPENING responses.php 
	IN YOUR BROWSER-THEY MUST BE KEPT UP TO DATE!
	These are case INsensitive. 
*/
//====================================================================
$already_ckdout_to_you='Item already charged to this user'; //item already out to this borrower response

//====================== Wording, SMTP, & Other Variables ==============
$currency_symbol='$';
$due_date_format='n/j/Y'; //see http://php.net/manual/en/function.date.php for information on formatting dates
$inactivity_timeout=40000; //time of inactivity before showing inactive prompt (in milliseconds)
$patron_id_length=''; //length of patron barcode or other id (leave empty if this varies)
$online_catalog_url=''; 	/*leave blank if you don't have one or if your catalog does
							not allow renewals (this is for printing on the paper receipt and 
							sending in the email receipt info about renewing online)*/
							
//smtp (for emailing receipts)
$smtp_host="localhost"; 
$smtp_authentication=false;
$smtp_username='';
$smtp_pwd='';

//wording
$library_name= "Your Library Name";
$module_name='Self-Checkout Station'; //shows on pages/home.php and pages/checkout.php
$email_from_name="Your Library Name"; //library's email name
$email_from_address=""; //library's email address
$email_subject='Self-Checkout Receipt'; //subject of email receipt
$intro_screen_text="Scan your library card's barcode to begin"; //shown on pages/home.php
$welcome_screen_text="Scan an item's barcode to continue.";	//shown on includes/welcome.php
$welcome_screen_subtext="(most barcodes are inside items' front covers)";
$renewal_prompt_text='is already checked out to your account.<br />Would you like to try to renew it?';
$out_of_order_head='Out of Service'; //shown on pages/out_of_order.php
$out_of_order_text='We are working to fix the problem'; //shown on pages/out_of_order.php


//========================= Sounds & Images ==========================
	//sounds
$error_sound="sounds/error.mp3";
$welcome_sound="sounds/welcome.mp3";
$note_sound="sounds/note.mp3";

	//images  (you need to uncomment one -and only one- line from each group). 
/*
	Keep in mind these are not the image files names -they are just meant to trigger the showing 
	of the types of images listed here. For further customization, images are loaded in the following files: 
	pages/checkout.php , pages/home.php, and includes/welcome.php 
*/

	//======= group 1: home page images of library card =======
//$card_image='kpl';
$card_image='barcoded';
//$card_image='magnetic';

	//======= group 2: home and checkout page images of book ==
$item_image='barcoded';
//$item_image='nonbarcoded';


//======================= Action Balloons =======================
/*

The following settings determine what types of materials will prompt the self check to issue an
action message (a short message accompanied by a beep sound) upon checkout. You may want borrowers to unlock the cases of 
or desensitize certain types of items, for example, or give a reminder that a particular type of item has a 
shorter checkout period than other items like it. 

Each item that requires an action can have its action message triggered by 1) its item type OR 2) its permanent location.

Each action balloon requires 2 variables set up in the following format:

1) $action_balloon[item type OR permanent location]['action_message']=action message;
2) $action_balloon[item type OR permanent location]['trigger']='item type' OR 'permanent location' OR 'call number prefix';

2 examples:
$action_balloon['CD']['action_message']='Please place your CDs inside one of the plastic bags near this station';
$action_balloon['CD']['trigger']='permanent location';

$action_balloon['EXPRESS DVDS']['action_message']='Express DVDs checkout for 3 days';
$action_balloon['EXPRESS DVDS']['trigger']='item type';

*/
//======================================================================
$action_balloon_bg_color='#f1cae1'; //background color for action balloons
//$action_balloon['CD']['action_message']='Please place your CDs inside one of the plastic bags near this station';
//$action_balloon['CD']['trigger']='permanent location';

//==================================== Allowed IPs =======================
/*
	list each allowed ip on a new line as $allowed_ip[]='IP'; 
	example: $allowed_ip[]='192.168.0.2';
		   $allowed_ip[]='192.168.0.4';
*/
$allowed_ip[]=''; //leave empty if you've already limited access to the self check via your server (Apache, IIS, etc.)

//==================================== Don't edit below this line =======================
if (!in_array($_SERVER['REMOTE_ADDR'],$allowed_ip) && !empty($allowed_ip[0])){ 
	exit;
}
ini_set('display_errors', $display_php_errors);
?>