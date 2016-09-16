<div id="page_content">

<?php 
//keypad include
if ($allow_manual_userid_entry) { 
	include_once('includes/keypad.php');
}

$stationIP=$_SERVER['REMOTE_ADDR']; // does the request come from a known station?

?>
	<div id="fixedlogo">
		<img src="images/<?php echo $logo_image;?>" />
	</div>
	<div id="spinner">
	</div>
	<div id="banner_title">
		<h2>
			<span>&nbsp;<?php echo $library_name;?></span>
			<br /><?php echo $module_name;?>
		</h2>
	</div>
	<div class="corners" id="banner">
		<span id="swap">
			<img src="images/<?php echo $card_image;?>_card1.png" align="left" class="active" />
			<?php if ($card_image!='magnetic'){ ?>
				<img src="images/<?php echo $card_image;?>_card2.png" align="left"/>
			<?php }?>
		</span>
		<h2><?php echo $intro_screen_text;?></h2>
		
	
	<div id="error_message">
	
	</div><!-- response container for showing failed login/blocked patron messages -->
		</div>
		
			
	<!--  ============= form for submitting patron id ============= -->
	<div style="position: absolute;left:-10000px;height:1px;overflow:hidden">
		<form id="form">
			<input name="barcode" type="text" id="barcode" />
		</form>
	</div>
	<!--  ============= end form for submitting items ============= -->

	<!--  ============= finish/cancel buttons ============= -->
	<table>
	<?php if(!empty($location[$stationIP])){ // known and configured station for checkin ?>
	
		<tr>
	   			<td>
				<div class="ok_button button" id="checkin" title="selfcheck_button">
					<h1>Rückgabe</h1>
				</div>
				</td>
		</tr>
	<?php }?>	
		<tr>
				<td>
				<div class="ok_button button" id="register" title="selfcheck_button">
					<h1>Druckkarte registrieren</h1>
				</div>
				</td>

   </tr>
	</table>
	</div>

<script type="text/javascript">
$(document).ready(function(){
	$.get("http://localhost:2666/stop"); // no more items
	var divspinner = document.getElementById('spinner');
	var spinner = new Spinner();
	$('#form').submit(function(){
		tb_remove();
		$barcode=$('#barcode');
		// UH find out if this is just the 111111 barcode from FKI reader construction!
		if('111111'!=$barcode.val()){  // ignore ruhebarcode
		$response=$("#error_message");
		$response.html('<h2 style="color:#4d8b27"> Anmeldung.. </h2>');
		$response.show();
		spinner.spin(divspinner);
		var siptimeout=setTimeout(function(){ // bail out on account_check timeout
//			alert("sip fail");
			window.location.href='index.php?page=out_of_order';},8000);

		$.post("processes/account_check.php", { barcode: $barcode.val()},
			function(data){
//				setTimeout(function(){
				clearTimeout(siptimeout); // alles gut
					if (data=='out of order'){ //does the response indicate a failed sip2 connection
						window.location.href='index.php?page=out_of_order';
					} else if (data=='blocked account'){ //does the response indicate a blocked account
						// $.dbj_sound.play('<?php echo $error_sound;?>'); // no sound
// localize						$response.html('<h2 id="error_message"> <span style="text-decoration:blink">There\'s a problem with your account</span>. Please see a circulation clerk.</h2>');
						$response.html('<?php echo $err_account_blocked;?>');
						$response.show();
						setTimeout(function() { $('#error_message').hide(); },5000);
					} else if (data=='invalid account'){ //does the response indicate an invalid account
//						$.dbj_sound.play('<?php echo $error_sound;?>');
// localize						$response.html('<h2 id="error_message"> <span style="text-decoration:blink">There was a problem</span>. Please scan your card again.</h2>');
								$response.html('<?php echo $err_account_invalid;?>');
								$response.show();
								setTimeout(function() { $('#error_message').hide(); },5000);
					} else { //if everything is ok with the patron's account show the welcome screen
//						$("#page_content").html(data);
//						$.get("http://localhost:2666/next"); // call for the first item code
						window.location.href='index.php?page=checkout'; // jump directly to checkout screen
					}
//				}, 200);
				spinner.stop();
				
		},'json'); //responses from process/account_check.php are expectd to be in json
		} // all skipped if '11111' found
		$barcode.val('');
		$barcode.focus();
		return false;   
	});

	$('#checkin').click(function(){
		window.location.href='index.php?page=checkout&checkin=true'; // jump directly to checkout screen, change to checkin view
/*	
		$.post("processes/start_checkin.php", { },
				function(data){
					setTimeout(function(){
						if (data=='out of order'){ //does the response indicate a failed sip2 connection
							window.location.href='index.php?page=out_of_order';
						} else  { // SIP is up and running - go to the list of items
//							$("#page_content").html(data);
							window.location.href='index.php?page=checkout&checkin=true'; // jump directly to checkout screen, change to checkin view
						}
					}, 1000);
			},'json'); //responses from process/account_check.php are expectd to be in json
*/
		});

	$('#register').click(function(){
		window.location.href='index.php?page=register'; // jump directly to register function
	  });
});
</script>