<?php
session_start();
include_once('../includes/html2text.php'); 
include_once('../includes/phpmailer/class.phpmailer.php');
include_once('../config.php');

//make the mail text
// The "source" HTML you want to convert.
$html =$_POST['receipt'];

$h2t =& new html2text($html);

// Simply call the get_text() method for the class to convert
// the HTML to the plain text. Store it into the variable.
$receipt_text = $h2t->get_text();

$mail = new PHPMailer();
$mail->IsSMTP(); // telling the class to use SMTP
$mail->Host = $smtp_host; // SMTP server

if ($smtp_authentication){
	$mail->SMTPAuth = true; // turn on SMTP authentication
	$mail->Username = $smtp_username; // SMTP username
	$mail->Password = $smtp_pwd; // SMTP password
} else {
	$mail->SMTPAuth = false;
}

$mail->FromName = $email_from_name;
$mail->From = $email_from_address;//sender addy
$mail->AddAddress($_SESSION['email']);//recip. email addy

$mail->Subject = $email_subject;
$mail->Body = str_replace('Title:',"\n\nTitle:",$receipt_text)."\n\n\n\nPlease respond to this email with comments or suggestions regarding the ".$module_name.".";
$mail->WordWrap = 70;
$mail->Send(); 
?>
<script type="text/javascript">
setTimeout(
	function(){
		window.location.href="processes/logout.php";
	},
(1700));
</script>