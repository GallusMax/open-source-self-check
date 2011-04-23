<div id="prompt_box" class="prompt_box">
	<!-- buttons -->
	<div class="corners prompt">
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
</div>
<script type="text/javascript">
$(document).ready(function(){
	$("#loading").hide();
	tb_remove(); //hide any existing notices
	tb_show($('#prompt_box').html());
	$.dbj_sound.play($('#error'));
});
</script>