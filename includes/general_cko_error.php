<?php 
$uniq_id=uniqid();
?>
<div id="prompt_container_<?php echo $uniq_id;?>" class="prompt_container">
	<h1><?php 
		if (strlen($response_message)>60){
			echo wordwrap($response_message, 40, "<br />");
			echo ":<br/><em>".$title."</em>";
		} else {
			echo $response_message;
			echo ":<br/><em>".$title."</em>";
			}?>
	</h1>
	<div class="ok_button button" title="selfcheck_button">
		<h1 onclic="tb_remove()">OK</h1>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$("#item_list .loading").hide();
	tb_remove(); //hide any existing notices
	tb_show($('#prompt_container_<?php echo $uniq_id;?>').html());
//	$.dbj_sound.play('<?php echo $error_sound;?>'); // UH silence!
});

// UH trigger another rfid barcode on user confirm without unsecuring anything
$(".ok_button").click(function(){
	tb_remove();
	$.get("http://localhost:2666/next"); 
});
</script>