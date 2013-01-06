<?php
/* 
	checkout processing page
*/
session_start();
include_once('../config.php');
include_once('../includes/sip2.php');
include_once('../includes/trimbylength.php');

//set some variables
$item_type='';
$call_number='';
$title=''; 
$permanent_location='';
$response_message='';
$due_date='';
$action_message='';
$RenewalOk='';
$OK='';

if (!empty($_SESSION['patron_barcode'])){
	if (empty($_POST['barcode'])){ //if for some reason the item barcode posted is empty fill in a bunk one
		$item_barcode='bunk_barcode';
	} else {
		$item_barcode=$_POST['barcode'];
	}

	$mysip = new sip2;
	
	// Set host name
	$mysip->hostname = $sip_hostname;
	$mysip->port = $sip_port;
	
	// Identify a patron
	$mysip->patron = $_SESSION['patron_barcode'];
	
	// connect to SIP server
	$connect=$mysip->connect();
		
		if(!$connect){ //if the connection failed go to the out of order page
			echo 	'<script type="text/javascript">
						$(document).ready(function(){
							window.location.href="index.php?page=out_of_order";
						});
					</script>';
			exit;
		}
		
	if(!empty($sip_login)){
		$sc_login=$mysip->msgLogin($sip_login,$sip_password);
		$mysip->parseLoginResponse($mysip->get_message($sc_login));
	}
			
	// get either renewal or checkout response
	if (!empty($_POST['renew'])){ //is this a renewal or no?
		$cko = $mysip->msgRenew($item_barcode, $sc_location);
		// parse the raw response into an array
		$checkout = $mysip->parseRenewResponse($mysip->get_message($cko));
	} else if (empty($_SESSION['cko_barcodes']) OR !in_array($item_barcode,$_SESSION['cko_barcodes'])){
		$cko = $mysip->msgCheckout($item_barcode, $sc_location);
		// parse the raw response into an array
		$checkout = $mysip->parseCheckoutResponse($mysip->get_message($cko));
	}
	
	//put the checkout or renewal response into variables
	if(!empty($checkout['fixed']['Ok'])){
		$OK=$checkout['fixed']['Ok'];
	}
	if (!empty($checkout['fixed']['RenewalOk'])){
		$RenewalOk=$checkout['fixed']['RenewalOk'];
	}
	if (!empty($checkout['variable']['AF'][0])){
		$response_message=trim($checkout['variable']['AF'][0]);//system response message
	}
	//get item info response
	$iteminfo = $mysip->msgItemInformation($item_barcode);
	// parse the raw response into an array
	$item = $mysip->parseItemInfoResponse( $mysip->get_message($iteminfo));
	
	//put the item info response into variables
	if (!empty($item['variable']['CR'][0])){
		$item_type=$item['variable']['CR'][0];
	}
	if (!empty($item['variable']['CS'][0])){
		$call_number=$item['variable']['CS'][0];
	}
	if (!empty($item['variable']['AQ'][0])){
		$permanent_location=$item['variable']['AQ'][0];
	}
	if (!empty($checkout['variable']['AJ'][0])){
		$title=$checkout['variable']['AJ'][0]; 
			if (stripos($title,'/')!==false){
				$title=substr($title,0,stripos($title,'/'));
			} 
		$title=ucwords(TrimByLength($title,45,false));
	}
	
	if ($OK!=1){

		if (((!empty($_SESSION['cko_barcodes']) && in_array($item_barcode,$_SESSION['cko_barcodes'])) OR stripos($response_message,$already_ckdout_to_you)!==false) && empty($_POST['renew'])){ //see if this item is already checked out to this user and show renew prompt if it is
		
			include_once('../includes/renew.php');
			exit;
		
		} else {

			//the item didn't get caught in any of our checkout exceptions so call the error prompt box
			include_once('../includes/general_cko_error.php');
			exit;
				
		} 
	
	}
	
	//add this item's barcode to the array of barcodes checked out this session
	$_SESSION['cko_barcodes'][]=$item_barcode;
	
	$ptrnmsg = $mysip->msgPatronInformation('charged'); //get checkout count again
	
	$patron_info = $mysip->parsePatronInfoResponse( $mysip->get_message($ptrnmsg));
	
	$_SESSION['checkouts']=$patron_info['fixed']['ChargedCount']; //checkouts
	$_SESSION['checkouts_this_session']=$_SESSION['checkouts_this_session']+1;
	
	$due_date=strtotime($checkout['variable']['AH'][0]);
	$due_date=date($due_date_format, $due_date);
	
	echo '
	<tr>
	<td class="cko_item" style="color:#666;width:25px" id="item_left_'.$item_barcode.'_'.$_SESSION['checkouts_this_session'].'">'.$_SESSION['checkouts_this_session'].'. </td>
	<td class="cko_item" style="width:80%;">'.$title.'</td>
	<td class="cko_item" id="item_right_'.$item_barcode.'_'.$_SESSION['checkouts_this_session'].'">'.$due_date.'</td>
	</tr>
	<script type="text/javascript">';
	//the javascript variables make up the elements of the receipt
	echo '
	var title="<tr><td>Title: '.str_replace('"','\"',$title).'</td></tr>";
	var call_number="<tr><td>Call Number: '.str_replace('"','\"',$call_number).'</td></tr>";
	var due_date="<tr><td>Date Due: '.$due_date.'</td></tr>";
	var item_barcode="<tr><td>Item ID: '.$item_barcode.'</td></tr>";
	
	var item='.implode('+',$receipt_item_list_elements).'+"<tr><td>&nbsp;</td></tr>";
	
	$(document).ready(function(){
		$("#item_list .loading,#pre_cko_buttons").hide();
		$("#cko_buttons").show();
		$("#cko_count").html("'.$_SESSION['checkouts'].'");
		$("#print_item_list table tbody").append(item);
		$("#item_list").attr({ scrollTop: $("#item_list").attr("scrollHeight") });
	';
	
	//Action Balloon
	if (!empty($action_balloon[$item_type]) && $action_balloon[$item_type]['trigger']=='item type'){	
		 $action_message=$action_balloon[$item_type]['action_message'];
	} else if (!empty($action_balloon[$permanent_location]) && $action_balloon[$permanent_location]['trigger']=='permanent location'){	
		 $action_message=$action_balloon[$permanent_location]['action_message'];
	}
		
	if (!empty($action_message) && empty($_SESSION['action_'.$item_type])){
		if (empty($_SESSION['action_balloon_count'])){ 	/*determine which side of the screen to show the action balloon (they'd overlap if consecutive items were to trigger balloons on the same side) */
			echo "$('.qtip').remove();"; //get rid on any existing balloons
			$action_balloon_position='target: "leftMiddle", tooltip: "rightMiddle"'; //if no previous action balloons put the balloon on the left
			$action_balloon_corner='rightMiddle';
			$_SESSION['action_balloon_count']=1; //set this variable so we know next time if there's an existing balloon
			$attach_to_element_id='item_left_'.$item_barcode.'_'.$_SESSION['checkouts_this_session'];
		} else {
			$action_balloon_position='target: "rightMiddle", tooltip: "leftMiddle"';
			$action_balloon_corner='leftMiddle';
			$_SESSION['action_balloon_count']=''; //reset balloon count
			$attach_to_element_id='item_right_'.$item_barcode.'_'.$_SESSION['checkouts_this_session'];
		}
	echo '
		$("#'.$attach_to_element_id.'").qtip( {
			content: "<p style=\'text-align:center;font-weight:bold;color:#333\'>'.str_replace('"','\"',$action_message).'</p>",
			show: { ready: true,effect:{type:"fade",length:0}},
			hide: { when: "never"},
			position: {corner: {'.$action_balloon_position.' }},
			style: { width:"140px",tip: {corner:"'.$action_balloon_corner.'",color:"'.$action_balloon_bg_color.'" }, border: { width: 1,color:"'.$action_balloon_bg_color.'" ,radius:5},background: "'.$action_balloon_bg_color.'"}
	 	});
		$.dbj_sound.play("'.$note_sound.'");';
		$_SESSION['action_'.$item_type]=1;
	} 
	//End Action Balloon
	 echo '
	});
	</script>';
	
	//end sip2 session
	$mysip->msgEndPatronSession();
	exit;
	
}

?>