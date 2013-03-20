<?php
/* 
	checkin SIP processing page
*/
session_start();
include_once('../config.php');
include_once('../includes/sip2.php');
include_once('../includes/trimbylength.php');

//set some variables
$item_type='';
$call_number='';
$title=''; 
$shorttitle='';
$permanent_location='';
$response_message='';
$due_date='';
$action_message='';
$RenewalOk='';
$OK='';
$sc_location="unbekannt";
$stationIP=$_SERVER['REMOTE_ADDR']; // does the request come from a known station?

	if(!empty($location[$stationIP]))
		$sc_location=$location[$stationIP];

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
//	$mysip->patron = $_SESSION['patron_barcode'];

	if (empty($_SESSION['cko_barcodes']) OR !in_array($item_barcode,$_SESSION['cko_barcodes'])){ // item not seen in this session
	
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
	
	
	$cki = $mysip->msgCheckin($item_barcode,$sc_location);
	// parse the raw response into an array
	$checkin = $mysip->parseCheckinResponse($mysip->get_message($cki));
	
		
	//put the checkout or renewal response into variables
	if(!empty($checkin['fixed']['Ok'])){
		$OK=$checkin['fixed']['Ok'];
	}

//	if (!empty($checkin['fixed']['RenewalOk'])){
//		$RenewalOk=$checkin['fixed']['RenewalOk'];
//	}

	if (!empty($checkin['variable']['AF'][0])){
		$response_message=trim($checkin['variable']['AF'][0]);//system response message
	}

/* 	
	//get item info response - what for? got the title already
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
*/
	if (!empty($checkin['variable']['AJ'][0])){
		$title=$checkin['variable']['AJ'][0]; 
			if (stripos($title,'/')!==false){
				$title=substr($title,0,stripos($title,'/'));
			} 
			
//		$title=ucwords(TrimByLength($title,75,false));  // dont ucwords..
		$title=(TrimByLength($title,75,false));
		$shorttitle=(TrimByLength($title,20,false));
	}
	
	}else{ // this item was here before..
		$OK=0;
		$response_message=$tx_already_seen;
	}
	
	if ($OK!=1){

/* 
 		// local check on double item ? will not happen..
 		if (((!empty($_SESSION['cko_barcodes']) && in_array($item_barcode,$_SESSION['cko_barcodes'])) OR stripos($response_message,$already_ckdout_to_you)!==false) && empty($_POST['renew'])){ //see if this item is already checked out to this user and show renew prompt if it is
		
			include_once('../includes/renew.php');  // UH - how to silently ignore a second booking?
			exit;
		
		} else {
*/
			//the item didn't get caught in any of our checkout exceptions so call the error prompt box
			include_once('../includes/general_cko_error.php');
			exit;
				
//		} 
	
	}
	
	//add this item's barcode to the array of barcodes checked out this session
	$_SESSION['cko_barcodes'][]=$item_barcode;
	
//	$ptrnmsg = $mysip->msgPatronInformation('charged'); //get checkout count again
//	$patron_info = $mysip->parsePatronInfoResponse( $mysip->get_message($ptrnmsg));
	
//	$_SESSION['checkouts']=$patron_info['fixed']['ChargedCount']; //checkouts
	$_SESSION['checkouts_this_session']=$_SESSION['checkouts_this_session']+1;
	
//	$due_date=strtotime($checkout['variable']['AH'][0]);
//	$due_date=date($due_date_format, $due_date);

	
	$reserved=preg_match('/'.$reservedPattern.'/',$response_message); // true, if "vorgemerkt" appears in SIP response
	if($reserved)
		$ckoresclass='cko_item cko_item_reserved';
	else
		$ckoresclass='cko_item';
	if(!empty($response_message))
		$returnString=$response_message;
	else 
		$returnString=$tx_returnOK;

			
	echo '
	<tr>
	<td class="cko_item" style="color:#666;width:25px" id="item_left_'.$item_barcode.'_'.$_SESSION['checkouts_this_session'].'">'.$_SESSION['checkouts_this_session'].'. </td>
	<td class="cko_item" style="width:80%;">'.$title.'</td>
	<td class="'.$ckoresclass.'" >'.$returnString.'</td>
	</tr>
	<script type="text/javascript">';
	//the javascript variables make up the elements of the receipt
	echo '
	var title="<td class=\"print_t\">'.str_replace('"','\"',$shorttitle).'</td>";
	var call_number="";
	var due_date="";
	var item_barcode="<td>'.$item_barcode.'</td>";
	
	var item="<tr>"+'.implode('+',$receipt_item_list_elements).'+"</tr>";

	$(document).ready(function(){ // UH is run on item checkout OK
		$("#item_list .loading,#pre_cko_buttons").hide();
		$("#cko_buttons").show();
		$("#print_item_list table tbody").append(item);
		$("#item_list").attr({ scrollTop: $("#item_list").attr("scrollHeight") });
		// here we know that $item_barcode has been charged! so trigger AFI change and the following one
		$.get("http://localhost:2666/on?'.$item_barcode.'");
				
	';
	
/*
	//Action Balloon
	if (!empty($action_balloon[$item_type]) && $action_balloon[$item_type]['trigger']=='item type'){	
		 $action_message=$action_balloon[$item_type]['action_message'];
	} else if (!empty($action_balloon[$permanent_location]) && $action_balloon[$permanent_location]['trigger']=='permanent location'){	
		 $action_message=$action_balloon[$permanent_location]['action_message'];
	}
		
	if (!empty($action_message) && empty($_SESSION['action_'.$item_type])){
		if (empty($_SESSION['action_balloon_count'])){ 	//determine which side of the screen to show the action balloon (they'd overlap if consecutive items were to trigger balloons on the same side) 
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
*/

	 echo '
	});
	</script>';
	
	//end sip2 session
//	$mysip->msgEndPatronSession();
	exit;


?>