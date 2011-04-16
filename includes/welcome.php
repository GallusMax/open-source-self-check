<div class="banner_title_wrapper">
	<h2 class="banner_title" style="color:#4d8b27">
		 <span style="font-size:.5em;">&nbsp;Welcome</span>
 		<br />
		<?php echo $_SESSION['name'];?>!
	</h2>
</div>
<div class="banner_wrapper" id="banner">
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
<div class="button_border" style="padding:5px;margin:100px auto 0 auto;cursor:pointer;width:200px;" onclick="$('#cancel').hide();$('#cancel_thanks').show();window.location.href='processes/logout.php'" id="cancel" title="selfcheck_button">
	<div class="cancel_button corners" title="selfcheck_button">
		<h1 title="selfcheck_button">Cancel</h1>
	</div>
</div>
<div class="button_border" id="cancel_thanks" style="padding:5px;margin:100px auto 0 auto;cursor:pointer;width:200px;display:none">
		<div class="thanks_button corners">
				<h1>Thanks</h1>
		</div>
</div>
<div style="position: absolute;left:-10000px;">
<!--  ============= form for submitting items ============= -->
	<form name="item" action="index.php?page=checkout" method="get">
		<input name="barcode" type="text" id="barcode"  value="" autocomplete="off" />
		<input name="page" type="hidden" id="page"  value="checkout" />
	</form>
<!--  ============= end form for submitting items ============= -->

</div>
<script type="text/javascript">
$(document).ready(function(){
	$('form').submit(function(){
		inactive_notice();
	});
	$barcode=$('#barcode');
	$barcode.val('');
	$barcode.focus();
	$('#banner,#cancel,#cancel_thanks').corners();
	$.dbj_sound.play($('#welcome'));
	inactive_notice();
});
$(document).click(function(){
	inactive_notice();
});
</script>


