<div id="prompt_container">
	<!-- buttons -->
	<h1 style="font-style:italic;white-space:nowrap"><?php echo $title;?></h1>
	<h1><?php echo $renewal_prompt_text;?></h1>
	<table style="width:50%">
		<tr>
			<td>
				<div class="prompt_box_border corners" style="padding:5px;margin:10px 10px 10px auto;cursor:pointer;width:150px" id="yes" onclick="tb_remove();renew('<?php echo $_POST['barcode'];?>');" title="selfcheck_button">
					<div class="ok_button corners" title="selfcheck_button">
						<h1 style="color:#333;padding:25px;white-space:nowrap" title="selfcheck_button">Yes</h1>
					</div>
				</div>
			</td>
			<td>
				<div class="prompt_box_border corners" style="padding:5px;margin:10px auto 10px 10px;cursor:pointer;width:175px" onclick="tb_remove()" id="no" title="selfcheck_button">
					<div class="cancel_button corners selfcheck_button">
						<h1 title="selfcheck_button">No Thanks</h1>
					</div>
				</div>
			</td>
		</tr>
	</table>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$("#item_list .loading").hide();
	tb_remove(); //hide any existing notices
	tb_show($('#prompt_container').html());
	$.dbj_sound.play($('#error'));
});
</script>