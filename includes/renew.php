<?php 
$uniq_id=uniqid();

//get item information via SIP2 if we don't have it
if (empty($title)){
	$item_send = $mysip->msgItemInformation($item_barcode);
	$item_info = $mysip->parseItemInfoResponse($mysip->get_message($item_send));
	$title=$item_info['variable']['AJ'][0];
}
?>
<div id="prompt_container_<?php echo $uniq_id;?>" class="prompt_container">
	<!-- buttons -->
	<h1 style="font-style:italic;white-space:nowrap"><?php echo $title;?></h1>
	<h1><?php echo $renewal_prompt_text;?></h1>
	<table style="width:50%" cellpadding="10">
		<tr>

			<td>
				<div class="ok_button button" oncli="tb_remove();renew('<?php echo $_POST['barcode'];?>');" title="selfcheck_button">
					<h1>Ok</h1>
				</div>
			</td>
			<!--  td>
				<div class="cancel_button button" onclick="tb_remove()" title="selfcheck_button">
					<h1>Ok</h1>
				</div>
			</td -->
		</tr>
	</table>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$("#item_list .loading").hide();
	tb_remove(); //hide any existing notices
	tb_show($('#prompt_container_<?php echo $uniq_id;?>').html());
//	$.dbj_sound.play('<?php echo $error_sound;?>');
	
});

//UH trigger another rfid barcode on user confirm without unsecuring anything
$(".ok_button").click(function(){
	tb_remove();
//	renew('<?php echo $_POST['barcode'];?>');
	$.get("http://localhost:2666/next"); 
});

$(".cancel_button").click(function(){
	tb_remove();
	$.get("http://localhost:2666/next"); 
});

</script>