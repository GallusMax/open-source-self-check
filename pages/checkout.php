<?php 
/* 	checkout / in  screen */
$_SESSION['checkouts_this_session']=0;  // copied from start_checkin aka account_check

?>
<div id="cko_head">
	<h1>
		<span id="swap" style="z-index:1000">
			<img src="images/<?php echo $item_image;?>_item1_small.png" align="left" class="active" />
			<?php if ($item_image!='nonbarcoded'){ ?>
				<img src="images/<?php echo $item_image;?>_item2_small.png" align="left"/>
			<?php }?>
		</span>
		<span style="font-size:13px;">&nbsp;&nbsp;<?php echo $library_name;?></span><br />&nbsp;<span id="module_name"><?php echo $module_name;?></span>
		<br /><br />
	</h1>
</div>

<div id="cko_wrapper">
	<div>
		<?php if (isset($_SESSION['name'])){?>
		<a class="welcome">Ausleihe für <?php echo $_SESSION['name'];?></a>
		<?php } ?>
		<a class="tab">
		<?php if (isset($_SESSION['checkouts'])){?>
			Anzahl Ausleihen: <span id="cko_count"><?php echo $_SESSION['checkouts'];?></span>
		<?php } ?>
		<?php if (false && $show_available_holds){?>
			<span> |</span>
			Available Holds: <?php echo $_SESSION['available_holds'];?>
		<?php }
			if (false && $show_fines){?>
			<span> |</span>
			Fines: <?php echo $_SESSION['fines'];
			}?>
			<span> |</span>
			<?php if (isset($_SESSION['overdues'])){?>
			<font <?php if ($_SESSION['overdues']>0){?> style="text-decoration: blink;" <?php }?>>Gemahnt: <?php echo $_SESSION['overdues'];?></font>
			<?php } ?>
		</a>
	</div>
	<div id="cko_border">
	
		<h2></h2>
		<table cellpadding="3" cellspacing="0" class="cko_column_head" align="center">
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td style="width:80%">Titel</td>
					<td class="tddue">garantierte Leihfrist</td>
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
					<h1>Beleg</h1>
				</div>
				<div class="thanks_button button" id="print_thanks">
					<h1>Abgemeldet</h1>
				</div>
			</td>
			<?php if (isset($_SESSION['email']) && !empty($_SESSION['email']) && $allow_email_receipts){?>
			<td>
				<div class="ok_button button" id="email" title="selfcheck_button">
					<h1>Beleg per Email</h1>
				</div>
				<div class="thanks_button button" id="email_thanks">
					<h1>..ist unterwegs</h1>
				</div>
			</td>
			<?php }?>
			<td>
				<div class="ok_button button" id="no_print" title="selfcheck_button">
					<h1>Kein Beleg</h1>
				</div>
				<div class="thanks_button button corners" id="no_print_thanks">
					<h1>Danke!</h1>
				</div>
			</td>
		</tr>
	</table>
	<div id="pre_cko_buttons">
		<div class="cancel_button button" title="selfcheck_button">
			<h1>Cancel</h1>
		</div>
		<div class="thanks_button button">
			<h1>Abgemeldet</h1>
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
			<tr class="underline">
				<td>MedienId</td><td>Titel</td><td class="tddue">Frist</td>
			</tr>
		</tbody>
	</table>
</div>
<!--  ============= end receipt container ============= -->

<script type="text/javascript">
			
var checkin=false;
var processItem="processes/checkout.php";
var tx_checkout="<?php echo $tx_checkout?>";
var tx_checkin="<?php echo $tx_checkin?>";
var patron_barcode="<?php if (isset($_SESSION['patron_barcode'])){echo $_SESSION['patron_barcode']; } ?>";


<?php if (isset($_GET['checkin'])){ // reuse checkout page for checkin now
	echo 'checkin=true; processItem="processes/checkin.php"';
}?>

$(document).ready(function() { 
//	alert("ready");
	
	if(checkin){
		$('#module_name').html(tx_checkin); // announce we are checkin in now
		$('.tddue').html('');
	};
			
	$('#pre_cko_buttons .cancel_button').click(
		function(){
			$(this).hide();
			$.get("http://localhost:2666/stop"); // no more items
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
		echo 'receipt_footer="<tr><td/></tr><tr><td colspan=3>'.str_replace("'","\'",implode("</td></tr><tr><td colspan=3>",$receipt_footer)).'</td></tr>";';
	}
	if (!empty($receipt_header)){
		echo 'receipt_header="<tr><td colspan=3>'.str_replace("'","\'",implode("</td></tr><tr><td colspan=3>",$receipt_header)).'</td></tr>";';
	}?>
	$("#print").click( //receipt print function
		function(){
			$.get("http://localhost:2666/stop"); // no more items
			//alert($("#print_item_list table tbody").html());
		if(checkin) // no patron known - mark this as return bill instead
			$('#print_item_list table tbody').prepend("<tr><td colspan=3>zurückgegebene Medien</td></tr>");
		else
			$('#print_item_list table tbody').prepend("<tr><td>Karte Nummer</td><td colspan=2>"+patron_barcode+"</td></tr>");
	
		$("#print_item_list table tbody").prepend("<tr><td colspan=3>Datum &nbsp;<?php echo date($due_date_format) ?></td></tr>");
		$("#print_item_list table tbody").prepend(receipt_header).append(receipt_footer);
		$('#no_print,#email').css('visibility','hidden');
		$(this).hide();
		$("#print_thanks").show();
//		alert($('#print_item_list'));
// setting prefs is not allowed?
//		prefs.set('print.always_print_silent',true);
		window.print(false);
//		alert('printing');
		setTimeout(function(){
				window.location.href='processes/logout.php'
		},1500);
	}); 
	
	$("#email").click( //receipt email function
		function(){
			$.get("http://localhost:2666/stop"); // no more items
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
			$.get("http://localhost:2666/stop"); // no more items
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
		$barcode=$('#barcode');
		tb_remove();
		if('111111'!=$barcode.val()){
		inactive_notice();
		$("#item_list .loading").show();
		$.post(processItem, { barcode: $barcode.val()},
			function(data){
				$("#item_list table").find('tbody').append(data);
				// UH code containing AFI_OFF rfid trigger (or not) is included in data!
				$("#item_list").scrollTop(500);
				//alert($("#item_list").scrollTop());
						
			});
		}else{ // card was drawn
			$.get("http://localhost:2666/stop"); // no more items
			$('#print,#email').css('visibility','hidden');
			$(this).hide();
			$("#no_print_thanks").show();
			setTimeout(
			function(){
				window.location.href="processes/logout.php";
				},
			(1000));  
		}
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

	$.get("http://localhost:2666/next"); // trigger next item
	
});


function post_first_item(item){ //post first item function
	$.post("processes/checkout.php", { barcode:item},
		function(data){
			$("#item_list table").find('tbody').append(data)
			// UH code containing AFI_OFF rfid trigger (or not) is included in data!
});
	return false;   
}

function renew(item){ //renew item function
	$barcode=$('#barcode');
	$.post("processes/checkout.php", { barcode:item,renew:'true'},
		function(data){
		$("#item_list table").find('tbody').append(data);
		$("#item_list").scrollTop(500);
		
		});
	$barcode.val('');
	$barcode.focus();
	return false;   
} 
</script>