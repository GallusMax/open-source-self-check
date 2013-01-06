<?php 
$uniq_id=uniqid();
?>
<div id="prompt_container_<?php echo $uniq_id;?>" class="prompt_container">
	<h1><?php 
		if (strlen($response_message)>60){
			echo wordwrap($response_message, 40, "<br />");
		} else {
			echo $response_message;
		}?>
	</h1>
	<div class="ok_button button" title="selfcheck_button">
		<h1 onclick="tb_remove()">OK</h1>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$("#item_list .loading").hide();
	tb_remove(); //hide any existing notices
	tb_show($('#prompt_container_<?php echo $uniq_id;?>').html());
	$.dbj_sound.play('<?php echo $error_sound;?>');
});
</script>