<?php
/* 
get your system's sip2 repsonse messages for particular transactions
*/
include_once('config.php');
include_once('includes/sip2.php');

$formaction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $formaction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$error=array();

if (!empty($_POST['form1']) OR !empty($_POST['sc_policies'])){

		$mysip = new sip2;

		// Set host name
		$mysip->hostname = $sip_hostname;
		$mysip->port = $sip_port;
	
	if (!empty($_POST['sc_policies'])){

		// connect to SIP server
		$mysip->connect();
			
		if(!empty($sip_login)){
			$sc_login=$mysip->msgLogin($sip_login,$sip_password);
			$mysip->parseLoginResponse($mysip->get_message($sc_login));
		}
		
		$sc = $mysip->msgSCStatus();
		$self_check_status = $mysip->parseACSStatusResponse($mysip->get_message($sc));
 
		$sc_checkouts_allowed=$self_check_status['fixed']['Checkout'];
		$sc_renewals_allowed=$self_check_status['fixed']['Renewal'];

	} else {
		if (empty($_POST['item'])){
			$error[]="You didn't enter an item barcode.";
    		} 
    		if (empty($_POST['patron']) && empty($_POST['ckdout'])){
    		$error[]="You didn't enter a patron barcode.";
    		} 
		if (empty($error)){ //no errors so far go with the sip2
			// Identify a patron
			$mysip->patron = $_POST['patron'];
			
			if(!empty($sip_login)){
				$sc_login=$mysip->msgLogin($sip_login,$sip_password);
				$mysip->parseLoginResponse($mysip->get_message($sc_login));
			}
			// connect to SIP server
			$mysip->connect();
			
			$cko = $mysip->msgCheckout($_POST['item']);
			
			// parse the raw response into an array
			$checkout = $mysip->parseCheckoutResponse( $mysip->get_message($cko));
	
			$response_message=trim($checkout['variable']['AF'][0]);

	}
}
}
?>
<html>
<body style="font-family: Arial;font-size:13px;background-image:url('images/gradient.png');background-repeat:repeat-x;">
	<h1 style="width:50%;margin:0 auto 0 auto">Response Messages</h1>
	<form action="<?php echo $formaction;?>" method="post" name="form1">
		<table style="width:50%" align="center">
		<tr>
				<td style="width:20%;white-space:nowrap">patron id/barcode:</td>
				<td><input type="text" name="patron" /><input type="hidden" name="form1" value="1" /></td>
			</tr>
			<tr>
				<td style="width:20%;white-space:nowrap">item id/barcode:</td>
				<td><input type="text" name="item" /> <input type="submit" /></td>
			</tr>
<?php 
	if (!empty($error)){?>
			<tr>
				<td style="width:20%;">&nbsp;</td>
				<td style="background-color:red;color:white;font-weight:bold">
				<?php echo implode(' ',$error);?>
				</td>
			</tr>
<?php 
	}
	if (!empty($response_message)){?>
			<tr>
				<td style="width:20%;">Response:</td>
				<td style="background-color:#ffff99;font-weight:bold">
				<?php echo $response_message;?>
				</td>
			</tr>
<?php } else if (!empty($sc_checkouts_allowed) OR !empty($sc_renewals_allowed)){
		$config=0;?>
			<tr>
				<td style="width:20%;vertical-align:top;white-space:nowrap">Checkouts Allowed (Y or N):</td>
				<td style="background-color:#ffff99;font-weight:bold">
				<?php echo $sc_checkouts_allowed;
					if ($sc_checkouts_allowed=='N'){ $config=1;?>(change your sip2 configuration to allow checkouts)<?php }?>
				</td>
			</tr>
			<tr>
				<td style="width:20%;vertical-align:top;white-space:nowrap">Renewals Allowed (Y or N):</td>
				<td style="background-color:#ffff99;font-weight:bold">
				<?php echo $sc_renewals_allowed;
					if ($sc_renewals_allowed=='N'){ $config=1;?>(consider changing your sip2 configuration to allow renewals)<?php }?>
				</td>
			</tr>
			<?php if ($config==0){?>
			<tr>
				<td style="width:20%;vertical-align:top">Configuring Your SIP2 Server:</td>
				<td style="background-color:#ffff99;font-weight:bold;vertical-align:top">
					<ol>
						<li style="margin-bottom:10px">
						Find you SIP2 configuration file on your server (it might be called sip2.cfg -check your vendor's SIP2 documentation)
						</li>
						<li style="margin-bottom:10px">
						Check your vendor's SIP2 documentation for configuring your SIP2 server's settings.
						</li>
				</td>
			</tr>
<?php 		}
}?>
			<tr>
				<td colspan="2">
					<ol>
						<li style="margin-bottom:10px">
						Before doing anything we want to see how your sip2 server is configured (you should proably look at your config files and your vendor's documentation but you can start here -see the installation instructions). In particular we want to see if it allows checkouts and renewals. <a href="#" onclick="document['form2'].submit();return false;">Click here to take a look.</a>
						</li>
						<li style="margin-bottom:10px">
						Enter a borrower's barcode/id above and the barcode/id of an item checked out to that borrower to show the response when trying to check out an item to a borrower who already has that item out.<br />
					The response message should be entered into the config file next to <br />$already_ckdout_to_you (i.e. $already_ckdout_to_you=" <em>whatever your response is</em> ")
					</li>

					</ol>
					
				</td>
			</tr>
	</table>
</form>
<form action="<?php echo $formaction;?>" method="post" name="form2">
<input type="hidden" value="1" name="sc_policies"/>
</form>
</body>
</html>