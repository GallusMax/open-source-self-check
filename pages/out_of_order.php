<div id="page_content">
	<div id="banner_title">
		<h2>
 		<span>&nbsp;<?php echo $module_name;?></span>
 		<br /><?php echo $out_of_order_head;?>
		</h2>
	</div>
	<div id="banner" class="corners">
			<span id="swap">
			<img src="images/out_of_order1.png" align="left" class="active" />
			<img src="images/out_of_order2.png" align="left"/>
			</span>
			<h2><?php echo $out_of_order_text;?></h2>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$.dbj_sound.play('<?php echo $error_sound;?>');
	window.setInterval( //do an ACS status request every 30 seconds to see if we're back online
		function() {
			$.get("processes/acs_status_check.php",
				function(data){
					if (data=='online'){
						window.location.href='index.php?page=home';
					}
				}
			, 'json')
		}
	,30000); 
});
</script>

