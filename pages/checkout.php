<?php 
/* 	checkout screen */
?>
<div class="cko_head">
	<h1>
		<span id="swap" style="z-index:1000">
		<img src="images/<?php echo $item_image;?>_item1_small.png" align="left" class="active" />
		<?php if ($item_image!='nonbarcoded'){ ?>
		<img src="images/<?php echo $item_image;?>_item2_small.png" align="left"/>
		<?php }?>
		</span>
		<span style="font-size:13px;">&nbsp;&nbsp;<?php echo $library_name;?></span><br />&nbsp;<?php echo $module_name;?><br /><br />
	</h1>
</div>
<div id="wrapper">
	<div style="text-align:right">
		<a class="rounded welcome">Welcome <?php echo substr($_SESSION['name'],0,strpos($_SESSION['name'],' '));?>!</a>
		<a class="rounded tab">
			Checkouts: <span id="checkout_count"><?php echo $_SESSION['checkouts'];?></span>
		<?php if ($show_available_holds){?>
			<span style="color:#fff"> |</span>
			Available Holds: <?php echo $_SESSION['available_holds'];?>
		<?php }
			if ($show_fines){?>
			<span style="color:#fff"> |</span>
			Fines: <?php echo $_SESSION['fines'];
			}?>
			<span style="color:#fff"> |</span>
			<span<?php if ($_SESSION['overdues']>0){?> style="text-decoration: blink;" <?php }?>>Overdues: <?php echo $_SESSION['overdues'];?></span>
		</a>
	</div>
	<div id="cko_wrapper">
		<div class="white">
			<h2 class="item_list_title">Items Checked Out Today</h2>
			<table border="0" cellpadding="3" cellspacing="0" style="width:100%;font-weight:bold" align="center">
				<tbody>
					<tr>
						<td class="cko_column_head" style="width:25px">&nbsp;</td>
						<td class="cko_column_head" style="width:80%">Title</td>
						<td class="cko_column_head">Due Date</td>
					</tr>
				</tbody>
			</table>
<!--  ============= checked out items container ============= -->
			<div id="item_list_div">
				<table border="0" cellpadding="3" cellspacing="0" style="width:100%;font-weight:bold" id="items_out" align="center">
					<tbody>
					</tbody>
				</table>
				<div style="width:16px;height:11px;margin:5px auto 5px auto;display:none;" id="loading"><img src="images/checking_account.gif"/></div>
			</div>
		</div>
	</div>
<!--  ============= finish/cancel buttons ============= -->
	<table width="70%" align="center" id="user_cko_buttons" style="display:none" cellpadding="5">
		<tr>
			<td style="width:33%">
				<div class="cko_page_button_border" id="print" title="selfcheck_button">
					<div class="ok_button print" title="selfcheck_button" id="print_inner">
						<h1 title="selfcheck_button">Print Receipt</h1>
					</div>
				</div>
				<div class="cko_page_button_border thanks_border" id="print_thanks">
					<div class="thanks_button" id="print_thanks_inner">
						<h1>Thanks</h1>
					</div>
				</div>
			</td>
			<?php if (!empty($_SESSION['email']) && $allow_email_receipts){?>
			<td style="width:34%">
				<div class="cko_page_button_border" id="email" title="selfcheck_button">
					<div class="ok_button print" title="selfcheck_button" id="email_inner">
						<h1 title="selfcheck_button">Email Receipt</h1>
					</div>
				</div>
				<div class="cko_page_button_border thanks_border" id="email_thanks">
					<div class="thanks_button corners" id="email_inner_thanks">
						<h1>Thanks</h1>
					</div>
				</div>
			</td>
			<?php }?>
			<td style="width:33%">
				<div class="cko_page_button_border" id="no_print" title="selfcheck_button">
					<div class="ok_button print" title="selfcheck_button" id="no_print_inner">
						<h1 title="selfcheck_button">No Receipt</h1>
					</div>
				</div>
				<div class="cko_page_button_border thanks_border" id="no_print_thanks">
					<div class="thanks_button" >
						<h1 id="inner_no_print_thanks">Thanks</h1>
					</div>
				</div>
			</td>
		</tr>
	</table>
	<div class="cko_page_button_border" id="cancel" title="selfcheck_button" onclick="window.location.href='processes/logout.php'">
		<div class="cancel_button" title="selfcheck_button" id="cancel_inner">
		<h1 title="selfcheck_button">Cancel</h1>
		</div>
	</div>
<!--  ============= end finish/cancel buttons ============= -->
</div>
<!--  ============= form for submitting items ============= -->
<div style="position: absolute;left:-1500px;">
	<form name="item" action="">
		<input name="barcode" type="text" id="barcode"  value="" autocomplete="off" />
	</form>
	<div id="print_item_list_div">
		<table cellpadding="0" cellspacing="0" width="100%" id="print_item_list_table">
			<tbody>
				<tr>
					<td>Checkout Receipt</td>
				</tr>
				<tr>
					<td><?php echo $library_name;?></td>
				</tr>
				<tr>
					<td style="font-style:italic">Renew your items online:</td>
				</tr>
				<tr>
					<td style="font-style:italic;"><?php echo $online_catalog_url;?></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() { 
$('#print,#print_inner,#print_thanks,#print_thanks_inner,#email,#email_inner,#email_thanks,#email_inner_thanks,#no_print,#no_print_inner,#no_print_thanks,#no_print_inner_thanks,#cancel,#cancel_inner').corners();

$(".tab").corners("top transparent");
$("#cko_wrapper").corners("left bottom transparent");
	
//////////////////receipts
$( "#print" ).click( //receipt print function
	function(){
	$('#no_print,#email').css('visibility','hidden');
	$('#print').hide();
	$("#print_thanks").show();
	$( "#print_item_list_div" ).print();
	return( false );
}); 
$( "#email" ).click( //receipt email function
	function(){
	$('#print,#no_print').css('visibility','hidden');
	$('#email').hide();
	$("#email_thanks").show();
	$( "#print_item_list_div" ).print('email');
	$.post("processes/email_receipt.php", { receipt:$('#print_item_list_div').html()},
	function(data){
	$('body').append(data);
	});
	return false;   
});
$("#no_print").click( //no print function
	function(){
	$('#print,#email').css('visibility','hidden');
	$('#no_print').hide();
	$("#no_print_thanks").show();
	setTimeout(
	function(){
		window.location.href="processes/logout.php";
		},
	(1700));  
});
//////////////////post checkouts function
$('form').submit(function(){
	tb_remove();
	inactive_notice();
	$("#loading").show();
	$barcode=$('#barcode');
	$.post("processes/checkout.php", { barcode: $barcode.val()},
		function(data){
		$("#items_out").find('tbody').append(data);
		});
	$barcode.val('');
	$barcode.focus();
	return false;   
	});
});
//////////////////posts
<?php if (isset($_GET['barcode'])){ //post first item 
if (empty($_GET['barcode'])){ //if for some reason the item barcode in the url is empty fill in a bunk one
$barcode='bunk_barcode';
} else {
$barcode=$_GET['barcode'];
}?>
post_first_item('<?php echo $barcode;?>')
<?php }?>

function post_first_item(item){ //post first item function
	$.post("processes/checkout.php", { barcode:item},
		function(data){
		$("#items_out").find('tbody').append(data)
	});
	return false;   
}

function renew(item){ //renew item function
	$barcode=$('#barcode');
	$.post("processes/checkout.php", { barcode:item,renew:'true'},
		function(data){
		$("#items_out").find('tbody').append(data)
		});
	$barcode.val('');
	$barcode.focus();
	return false;   
} 
</script>