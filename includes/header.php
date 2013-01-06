<?php 
/* 	page header etc. 
*/
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $library_name;?> - <?php echo $module_name;?></title>
<script type="text/javascript" src="processes/combine.php?type=javascript&files=jquery.js,sound.js,center.js,thickbox.js,countdown.js,qtip.js"></script>
<link type="text/css" rel="stylesheet" href="processes/combine.php?type=css&files=selfcheck.css,thickbox.css" />

<?php if ($hide_cursor_pointer){?>
<style type="text/css">
*, html{
	cursor:none !important;
}
</style>
<?php }?>

<script type="text/JavaScript">
function ajaxUpdater(id,url) {
$('#'+id).load(url);
}

//function to destroy all active qtips
function destroy_qtips() {
$('div.qtip[qtip]').each(function()
               {
                  if($(this).qtip('api').status.rendered === true)
                  {
                    $(this).qtip('destroy');
                  }
               })
}

///////////////////image rotator
function swapImages(){
  var $active = $('#swap .active');
  var $next = ($($active).next().length > 0) ? $($active).next() : $('#swap img:first');
    $active.hide().removeClass('active');
    $next.show().addClass('active');
}

$(document).ready(function() {
	$.dbj_sound.cache('<?php echo $error_sound;?>','<?php echo $welcome_sound;?>','<?php echo $note_sound;?>'); //cache sounds
	setInterval('swapImages()', 1000);
	$('#barcode').focus();
});

$(document).click(function() {
	$('#barcode').focus();
});

///////////////////session timeout scripts
function inactive_notice(){
	$.doTimeout( 'count'); //reset the redirect counter
	$.doTimeout( 'prompt', <?php echo $inactivity_timeout;?>, function(){ //do the following in <?php echo $inactivity_timeout;?> milliseconds (see config file to set this)
		tb_remove(); 
		tb_show($('#idle_timer').html());
		$.dbj_sound.play('<?php echo $error_sound;?>');
		countdown_redirect($("#prompt span"));
		$.doTimeout( 'count', 21000, function(){ //redirect in 21 seconds
		window.location.href="processes/logout.php";
		});
	});
}

<?php if ($page!='home' && $page!='out_of_order'){?>
$(document).ready(function() {
	inactive_notice();
});

$(document).click(function(){
	inactive_notice();
});
<?php } ?>
///////////////////end session timeout scripts
</script>
</head>
<body>
