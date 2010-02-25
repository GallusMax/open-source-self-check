<div id="page_content">

<?php 
//keypad include
if ($allow_manual_userid_entry) { 
	include_once('includes/keypad.php');
}
?>

	<div class="banner_title_wrapper">
		<h2 class="banner_title" >
 		<span style="font-size:.5em">&nbsp;<?php echo $library_name;?></span>
 		<br /><?php echo $module_name;?>
		</h2>
	</div>
	<div class="banner_wrapper" id="banner">
			<span id="swap">
			<img src="images/<?php echo $card_image;?>_card1.png" align="left" class="active" />
			<?php if ($card_image!='magnetic'){ ?>
			<img src="images/<?php echo $card_image;?>_card2.png" align="left"/>
			<?php }?>
			</span>
			<h2><?php echo $intro_screen_text;?></h2>
	</div>
	
	<div id="response"></div><!-- response container for showing failed login/blocked patron messages -->
	
	<div style="position: absolute;left:-10000px;height:1px;overflow:hidden">
	
		<!-- load these images so that they're in the cache -->
		<img src="images/<?php echo $item_image;?>_item1_big.png"/>
		<img src="images/<?php echo $item_image;?>_item1_small.png"/>
		<?php if ($item_image!='nonbarcoded'){ ?>
		<img src="images/<?php echo $item_image;?>_item2_big.png"/>
		<img src="images/<?php echo $item_image;?>_item2_small.png"/>
		<?php }
		
		//offscreen form for submitting patron barcode ?>
		<form id="patron_form" name="patron" action="processes/account_check.php" method="post">
		<input name="barcode" type="text" id="barcode" value="" autocomplete="off" />
		</form>
		
	</div>
</div>

<script type="text/javascript">
$(document).ready(function(){
	$('#banner').corners();
	$('form').submit(function(){
		tb_remove();
		$barcode=$('#barcode');
		$("#response").html('<h2 style="color:#4d8b27"> Checking your account please wait. <img src="images/checking_account.gif" /></h2>');
		$.post("processes/account_check.php", { barcode: $('#barcode').val()},
			function(data){
				setTimeout(function(){
				if (data=='out of order'){ //does the response indicate a failed sip2 connection
					window.location.href='index.php?page=out_of_order';
				} else if (data=='blocked account'){ //does the response indicate a blocked account
					$.dbj_sound.play($('#error'));
					$('#response').html('<h2 id="error_message"> <span style="text-decoration:blink">There\'s a problem with your account</span>. Please see a circulation clerk.</h2>');
					setTimeout(function() { $('#error_message').hide(); },10000);
				} else if (data=='invalid account'){ //does the response indicate an invalid account
					$.dbj_sound.play($('#error'));
					$('#response').html('<h2 id="error_message"> <span style="text-decoration:blink">There was a problem</span>. Please scan your card again.</h2>');
				setTimeout(function() { $('#error_message').hide(); },10000);
				} else { //if everything is ok with the patron's account show the welcome screen
					$("#page_content").html(data);
				}
			}, 1000);
		},'json'); //responses from process/account_check.php are expectd to be in json
		$barcode.val('');
		$barcode.focus();
		return false;   
	});
});
</script>