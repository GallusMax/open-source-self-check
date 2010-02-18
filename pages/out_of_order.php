<div id="page_content">
	<div class="banner_title_wrapper">
		<h2 class="banner_title" >
 		<span style="font-size:.5em">&nbsp;<?php echo $module_name;?></span>
 		<br /><?php echo $out_of_order_head;?>
		</h2>
	</div>
	<div class="banner_wrapper" id="banner">
			<span id="swap">
			<img src="images/out_of_order1.png" align="left" class="active" />
			<img src="images/out_of_order2.png" align="left"/>
			</span>
			<h2><?php echo $out_of_order_text;?></h2>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$('#banner').corners();
	$.dbj_sound.play($('#error'));
	window.setInterval( //do an ACS status request every 30 seconds to see if we're back online
		function() {
			$.get("processes/acs_status_check.php",
				function(data){
					if (data=='online'){
						window.location.href='index.php?page=home';
					}
				}
			)
		}
	,30000); 
});
</script>

