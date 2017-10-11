<?php
/**
* 	Self Check
*
* 	This application provides a method of checking out and renewing materials via a web interface using 3M's SIP2 standard
* 	Thanks to John Wohlers for his sip2 class -this application would not have come about without it
*	
*	If you make additions or other customizations that improve or extend the self check's 
*	functionality I would love to hear about them.
*
*	@author     	Eric Melton <ericmelton@gmail.com>
* 	@licence    	http://opensource.org/licenses/gpl-3.0.html
* 	@copyright  	Eric Melton <ericmelton@gmail.com>
*	@version    	1.2
*/

/**
 * local config, including hostnames and passwords is done in config_local.php now
 * which is included at the bottom of this file
 * please rename the config_local.php-dist and fill in your sensible data
 */

//========================== Site Rules ==============================
$sc_location='unknown';//enter a name for the self-check's location (e.g. 'East Branch') to track transactions in your SIP2 logs (in Polaris this is required and is the numeric organization ID)
$allow_manual_userid_entry=false;
$show_fines=true;
$show_available_holds=true;
$allow_email_receipts=false;
$display_php_errors='on'; //off or on
$hide_cursor_pointer=false; //hides default cursor pointer -should probably set to true on live self check

$ruhebarcode='111111'; // barcode reader feature: this code is read when card is drawn

//====================== SIP2 Responses  ==============
/*
	GET YOUR SYSTEM'S RESPONSE MESSAGES BY ENTERING YOUR SIP2 CONNECTION INFO ABOVE THEN OPENING responses.php 
	IN YOUR BROWSER-THEY MUST BE KEPT UP TO DATE!
	These are case INsensitive. 
*/
//====================================================================
$already_ckdout_to_you='Item already charged to this user'; //item already out to this borrower response

$online_catalog_url='http://ub.hsu-hh.de/DB=1/'; 	/*leave blank if you don't have one or if your catalog does
							not allow renewals (this is for printing on the paper receipt and 
							sending in the email receipt info about renewing online)*/

//====================== Wording, SMTP, & Other Variables ==============
$currency_symbol='EUR';
$due_date_format='j.n.Y'; //see http://php.net/manual/en/function.date.php for information on formatting dates
$inactivity_timeout=40000; //time of inactivity before showing inactive prompt (in milliseconds)
$account_check_timeout=15000; //time of inactivity after patron card scan before showing out of order page (in milliseconds)
$patron_id_length=''; //length of patron barcode or other id (leave empty if this varies)
$patron_id_pattern='/0705\d{6}[\dX]{1}/'; // regex pattern matching the patron barcode (leave empty to ignore)

//wording
$reservedPattern="vorgemerkt"; // this string in the SIP2 AF return message signalizes a reservation
$tx_returnOK="zurückgenommen";
$tx_returnReserved="bereits vorgemerkt";
$tx_already_seen="Medium bereits bearbeitet";
$tx_checkin_refused="Rücknahme nicht erlaubt";
$library_name= "Die Bibliothek der Helmut-Schmidt-Universit&auml;t";
$module_name=''; //shows on pages/home.php and pages/checkout.php
$tx_checkout='Ausleihe - max 6 Medien auf der Markierung ablegen!';
$tx_checkin='Rücknahme - max 6 Medien auf der Markierung ablegen!';
$email_from_name=""; //library's email name
$email_from_address=""; //library's email address
$admin_emails=''; //comma delimted list of email addresses that should be notified should the self-check go out of order
$email_subject='Ausleihquittung'; //subject of email receipt
$intro_screen_text="Scan your library card's barcode to begin"; //shown on pages/home.php
$intro_screen_text="Ausleihe? Bitte Medien auflegen und Ausweis einlesen."; //shown on pages/home.php
$welcome_screen_text="Scan an item's barcode to continue";	//shown on includes/welcome.php
$welcome_screen_subtext="(most barcodes are inside items' front covers)";
$renewal_prompt_text='is already checked out to your account.<br />Would you like to try to renew it?';
$renewal_prompt_text='bereits auf Ihrem Konto';
$out_of_order_head='Out of Service'; //shown on pages/out_of_order.php
$out_of_order_text='We are working to fix the problem'; //shown on pages/out_of_order.php
$err_account_blocked="There\'s a problem with your account. Please see a circulation clerk.";
$err_account_blocked="<h1>Keine Ausleihe erlaubt.</h1><h3>Bitte fragen Sie an der Theke.</h3>";
$err_account_invalid="There was a problem. Please scan your card again.";
$err_account_invalid="<h1>Karte nicht erkannt.</h1><h3>Bitte stecken Sie die Karte mit dem Barcode nach hinten in den Leseschlitz.</h3>";

//====================== Paper & Email Receipts ==============
/* add elements to or remove elements from the header & footer arrays below to manipulate that piece of the receipt.
the elements will appear on separate lines of the receipt in the order that you place them below */ 
$receipt_header[]='Buchungsbeleg';
$receipt_header[]=$library_name;
$receipt_footer[]='Automatische Verl&auml;ngerungen - sofern keine Vormerkung erfolgt:';
$receipt_footer[]='&nbsp;&nbsp;Externe:&nbsp;&nbsp;       21 Tage';
$receipt_footer[]='&nbsp;&nbsp;Uniangeh&ouml;rige:&nbsp;  3 Monate';
$receipt_footer[]='(keine autom. Verl&auml;ngerung für Zeitschriften)';

$receipt_footer[]=$online_catalog_url;

/*place the following in the order you want the elements to appear in the item list on the 
paper and email receipts. remove (or comment out) any elements you don't want included.
element options include item_barcode, title, due_date, and call_number */
$receipt_item_list_elements[]='item_barcode';
$receipt_item_list_elements[]='title';
//$receipt_item_list_elements[]='call_number';
$receipt_item_list_elements[]='due_date';

//========================= Sounds & Images ==========================
	//sounds
$error_sound="sounds/error.mp3";
$welcome_sound="sounds/welcome.mp3";
$note_sound="sounds/note.mp3";
$error_sound="";
$welcome_sound="";
$note_sound="";

	//images  (you need to uncomment one -and only one- line from each group). 
//$logo_image='bhsu-Logo-small_grau.png';
$logo_image='Biblogofrei.png';
/*
	Keep in mind these are not the image files names -they are just meant to trigger the showing 
	of the types of images listed here. For further customization, images are loaded in the following files: 
	pages/checkout.php , pages/home.php, and includes/welcome.php 
*/

	//======= group 1: home page images of library card =======
//$card_image='kpl';
$card_image='barcoded';
$card_image='rfid';
//$card_image='magnetic';

	//======= group 2: home and checkout page images of book ==
$item_image='barcoded';
//$item_image='nonbarcoded';
$item_image='piled';


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

//==================================== Local Settings ====================
// keep installation-specific settings in local config file 
// keep confidential settings out of repository
include_once("config_local.php");

//==================================== Don't edit below this line =======================
if (!in_array($_SERVER['REMOTE_ADDR'],$allowed_ip) && !empty($allowed_ip[0])){ 
	exit;
}
ini_set('display_errors', $display_php_errors);
?>
