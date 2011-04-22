<div id="prompt_box" class="prompt_box">
	<!-- buttons -->
	<div class="corners prompt">
		<h1><?php 
			if (strlen($response_message)>60){
				echo wordwrap($response_message, 40, "<br />");
			} else {
				echo $response_message;
			}?></h1>
		<div class="prompt_box_border corners selfcheck_button" style="padding:5px;margin:10px auto 10px auto;cursor:pointer;width:150px" id="ok">
			<div class="ok_button corners">
				<h1 style="color:#333;padding:25px;white-space:nowrap" title="selfcheck_button" onclick="tb_remove()">OK</h1>
			</div>
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