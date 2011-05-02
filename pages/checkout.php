<?php 
/* 	checkout screen */
?>
<div id="cko_head">
	<h1>
		<span id="swap" style="z-index:1000">
			<img src="images/<?php echo $item_image;?>_item1_small.png" align="left" class="active" />
			<?php if ($item_image!='nonbarcoded'){ ?>
				<img src="images/<?php echo $item_image;?>_item2_small.png" align="left"/>
			<?php }?>
		</span>
		<span style="font-size:13px;">&nbsp;&nbsp;<?php echo $library_name;?></span><br />&nbsp;<?php echo $module_name;?>
		<br /><br />
	</h1>
</div>

<div id="cko_wrapper">
	<div>
		<a class="welcome">Welcome <?php echo substr($_SESSION['name'],0,strpos($_SESSION['name'],' '));?>!</a>
		<a class="tab">
			Checkouts: <span id="cko_count"><?php echo $_SESSION['checkouts'];?></span>
		<?php if ($show_available_holds){?>
			<span> |</span>
			Available Holds: <?php echo $_SESSION['available_holds'];?>
		<?php }
			if ($show_fines){?>
			<span> |</span>
			Fines: <?php echo $_SESSION['fines'];
			}?>
			<span> |</span>
			<font <?php if ($_SESSION['overdues']>0){?> style="text-decoration: blink;" <?php }?>>Overdues: <?php echo $_SESSION['overdues'];?></font>
		</a>
	</div>
	<div id="cko_border">
	
		<h2>Items Checked Out Today</h2>
		<table cellpadding="3" cellspacing="0" class="cko_column_head" align="center">
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td style="width:80%">Title</td>
					<td>Due Date</td>
				</tr>
			</tbody>
		</table>
		
<!--  ============= checked out items container ============= -->
		<div id="item_list">
			<table border="0" cellpadding="3" cellspacing="0" align="center">
				<tbody>
				</tbody>
			</table>
			<div class="loading"><img src="images/checking_account.gif"/></div>
		</div>
<!--  ============= end checked out items container ============= -->

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
	<div id="pre_cko_buttons">
		<div class="cancel_button button" title="selfcheck_button">
			<h1>Cancel</h1>
		</div>
		<div class="thanks_button button">
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
</div>
<!--  ============= end form for submitting items ============= -->

<!--  ============= receipt container ============= -->
<div id="print_item_list">
	<table>
		<tbody>
			<tr>
				<td>&nbsp;</td>
			</tr>
		</tbody>
	</table>
</div>
<!--  ============= end receipt container ============= -->

<script type="text/javascript">
$(document).ready(function() { 
	$('#pre_cko_buttons .cancel_button').click(
		function(){
			$(this).hide();
			$('#pre_cko_buttons .thanks_button').show();
			setTimeout(function(){
				window.location.href='processes/logout.php'
			},1000);
		}
	);
	//////////////////receipts
	var receipt_footer;
	var receipt_header;
	<?php 
	if (!empty($receipt_footer)){
		echo 'receipt_footer="<tr><td>'.str_replace("'","\'",implode("</td></tr><tr><td>",$receipt_footer)).'</td></tr>";';
	}
	if (!empty($receipt_header)){
		echo 'receipt_header="<tr><td>'.str_replace("'","\'",implode("</td></tr><tr><td>",$receipt_header)).'</td></tr>";';
	}?>
	$("#print").click( //receipt print function
		function(){
		$("#print_item_list table tbody").prepend(receipt_header).append(receipt_footer);
		$('#no_print,#email').css('visibility','hidden');
		$(this).hide();
		$("#print_thanks").show();
		print();
		setTimeout(function(){
				window.location.href='processes/logout.php'
		},1500);
	}); 
	
	$("#email").click( //receipt email function
		function(){
		$("#print_item_list table tbody").append(receipt_footer);
		$('#print,#no_print').css('visibility','hidden');
		$(this).hide();
		$("#email_thanks").show();
		$.post("processes/email_receipt.php", { receipt:$('#print_item_list').html() },
		function(data){
			$('body').append(data);
		});
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
			(1000));  
	});
	//////////////////post checkouts function
	$('#form').submit(function(){
		tb_remove();
		inactive_notice();
		$("#item_list .loading").show();
		$barcode=$('#barcode');
		$.post("processes/checkout.php", { barcode: $barcode.val()},
			function(data){
				$("#item_list table").find('tbody').append(data);
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
			$("#item_list table").find('tbody').append(data)
	});
	return false;   
}

function renew(item){ //renew item function
	$barcode=$('#barcode');
	$.post("processes/checkout.php", { barcode:item,renew:'true'},
		function(data){
			$("#item_list table").find('tbody').append(data)
		});
	$barcode.val('');
	$barcode.focus();
	return false;   
} 
</script>