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
				<table border="0" cellpadding="3" cellspacing="0" align="center">
					<tbody>
					</tbody>
				</table>
				<div class="loading"><img src="images/checking_account.gif"/></div>
			</div>
<!--  ============= end checked out items container ============= -->

		</div>
	</div>
<!--  ============= finish/cancel buttons ============= -->
	<table id="cko_buttons" cellpadding="5">
		<tr>
			<td>
				<div class="ok_button button" id="print" title="selfcheck_button">
					<h1>Print Receipt</h1>
				</div>
				<div class="thanks_button button" id="print_thanks">
					<h1>Thanks</h1>
				</div>
			</td>
			<?php if (!empty($_SESSION['email']) && $allow_email_receipts){?>
			<td>
				<div class="ok_button button" id="email" title="selfcheck_button">
					<h1>Email Receipt</h1>
				</div>
				<div class="thanks_button button" id="email_thanks">
					<h1>Thanks</h1>
				</div>
			</td>
			<?php }?>
			<td>
				<div class="ok_button button" id="no_print" title="selfcheck_button">
					<h1>No Receipt</h1>
				</div>
				<div class="thanks_button button corners" id="no_print_thanks">
					<h1>Thanks</h1>
				</div>
			</td>
		</tr>
	</table>
	<div style="width:250px;margin:10px auto 10px auto">
		<div class="cancel_button button" title="selfcheck_button" onclick="$(this).hide();$('#cancel_thanks').show();window.location.href='processes/logout.php'">
			<h1>Cancel</h1>
		</div>
		<div class="thanks_button button" id="cancel_thanks">
			<h1>Thanks</h1>
		</div>
	</div>
<!--  ============= end finish/cancel buttons ============= -->

</div>

<!--  ============= form for submitting items ============= -->
<div style="position: absolute;left:-1500px;">
	<form id="form">
		<input name="barcode" type="text" id="barcode" />
	</form>
<!--  ============= end form for submitting items ============= -->

<!--  ============= receipt container ============= -->
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
<!--  ============= end receipt container ============= -->

</div>

<script type="text/javascript">
$(document).ready(function() { 
	//////////////////receipts
	$( "#print" ).click( //receipt print function
		function(){
		$('#no_print,#email').css('visibility','hidden');
		$(this).hide();
		$("#print_thanks").show();
		$( "#print_item_list_div" ).print();
		return( false );
	}); 
	
	$( "#email" ).click( //receipt email function
		function(){
		$('#print,#no_print').css('visibility','hidden');
		$(this).hide();
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
		$(this).hide();
		$("#no_print_thanks").show();
		setTimeout(
		function(){
			window.location.href="processes/logout.php";
			},
		(1700));  
	});
	//////////////////post checkouts function
	$('#form').submit(function(){
		tb_remove();
		inactive_notice();
		$("#loading").show();
		$barcode=$('#barcode');
		$.post("processes/checkout.php", { barcode: $barcode.val()},
			function(data){
				$("#item_list_div table").find('tbody').append(data);
			});
		$barcode.val('');
		$barcode.focus();
		return false;   
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
	
});


function post_first_item(item){ //post first item function
	$.post("processes/checkout.php", { barcode:item},
		function(data){
			$("#item_list_div table").find('tbody').append(data)
	});
	return false;   
}

function renew(item){ //renew item function
	$barcode=$('#barcode');
	$.post("processes/checkout.php", { barcode:item,renew:'true'},
		function(data){
			$("#item_list_div table").find('tbody').append(data)
		});
	$barcode.val('');
	$barcode.focus();
	return false;   
} 
</script>