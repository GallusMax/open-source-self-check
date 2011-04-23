<div id="banner_title">
	<h2 style="color:#4d8b27">
		 <span>&nbsp;Welcome</span>
 		<br />
		<?php echo $_SESSION['name'];?>!
	</h2>
</div>

<div id="banner" class="corners">
	<span id="swap">
		<img src="images/<?php echo $item_image;?>_item1_big.png" align="left" class="active" />
		<?php if ($item_image!='nonbarcoded'){ ?>
			<img src="images/<?php echo $item_image;?>_item2_big.png" align="left"/>
		<?php }?>
	</span>
	<h2>
		<?php echo $welcome_screen_text;?>
		<br />
		<span style="font-size:15px;color:#FCFCFC;font-style:italic"><?php echo $welcome_screen_subtext;?></span>
	</h2>
</div>

<div class="cancel_button button" style="margin:100px auto 0 auto;" onclick="$('#cancel').hide();$('#cancel_thanks').show();window.location.href='processes/logout.php'" id="cancel" title="selfcheck_button">
	<h1>Cancel</h1>
</div>
<div class="thanks_button button" id="cancel_thanks" style="margin:100px auto 0 auto;">
	<h1>Thanks</h1>
</div>

<!--  ============= form for submitting items ============= -->
<div style="position: absolute;left:-10000px;">
	<form id="form" action="index.php?page=checkout" method="get">
		<input name="barcode" type="text" id="barcode"  value="" autocomplete="off" />
	</form>
</div>
<!--  ============= end form for submitting items ============= -->

<script type="text/javascript">
$(document).ready(function(){
	$('#form').submit(function(){
		inactive_notice();
	});
	$barcode=$('#barcode');
	$barcode.val('');
	$barcode.focus();
	$.dbj_sound.play($('#welcome'));
	inactive_notice();
});
$(document).click(function(){
	inactive_notice();
});
</script>


