<div id="keypad_icon" >
	<table width="100%" cellpadding="3">
	    <tr>
	    	<td onclick="show_keypad()">
	    		<p title="selfcheck_button">manually</p>
	    		<p title="selfcheck_button">enter</p>
	    		<p title="selfcheck_button">your id</p>
	    	</td>
	    	<td onclick="show_keypad()" style="width:5px;"><img src="images/keypad_icon.gif" title="selfcheck_button"/></td>
	    </tr>
	</table>
</div>
<div id="keypad_container">
	<div class="corners prompt selfcheck_button">
		<table width="450" cellspacing="0" cellpadding="0" align="center" class="keypad">
			<tr>
				<td colspan="3" class="keypad_screen"></td>
			</tr>
			<tr>
				<td><div class="keypad_key corners" title="selfcheck_button">1</div></td>
				<td><div class="keypad_key corners" title="selfcheck_button">2</div></td>
				<td><div class="keypad_key corners" title="selfcheck_button">3</div></td>
			</tr>
			<tr>
				<td><div class="keypad_key corners" title="selfcheck_button">4</div></td>
				<td><div class="keypad_key corners" title="selfcheck_button">5</div></td>
				<td><div class="keypad_key corners" title="selfcheck_button">6</div></td>
			</tr>
			<tr>
				<td><div class="keypad_key corners" title="selfcheck_button">7</div></td>
				<td><div class="keypad_key corners" title="selfcheck_button">8</div></td>
				<td><div class="keypad_key corners" title="selfcheck_button">9</div></td>
			</tr>
			<tr>
				<td  onclick="delete_keypad_entry();"><div class="keypad_key corners" title="selfcheck_button"><span title="selfcheck_button">delete</span></div></td>
				<td><div class="keypad_key corners" title="selfcheck_button">0</div></td>
				<td><div class="corners" title="selfcheck_button"><span title="selfcheck_button" onclick="tb_remove();">cancel</span></div></td>
			</tr>
		</table>
		<div class="prompt_box_border corners" id="ok" onclick="$('#barcode').val($('#TB_ajaxContent .keypad_screen').text());$('#patron_form').submit();" title="selfcheck_button">
			<div class="ok_button corners" title="selfcheck_button">
				<h1 style="color:#333;padding:15px;white-space:nowrap" title="selfcheck_button">OK</h1>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
function show_keypad(){
	tb_remove();
	tb_show($('#keypad_container').html());
	var keypad_key=$('#TB_ajaxContent .keypad_key');
	$('.corners').corners();
	keypad_key.mousedown(function (){
		$(this).addClass('keypad_clicked');
	});
	keypad_key.mouseup(function (){
		$(this).removeClass('keypad_clicked');
	});
	keypad_key.click(function (){
		if ($(this).text()!='delete'){
			var keypad_window=$('#TB_ajaxContent .keypad_screen');
			if (keypad_window.text().length<19){
				keypad_window.append($(this).text());
			}
		}
	});

}
function delete_keypad_entry(){
	var keypad_window_string=$('#TB_ajaxContent .keypad_screen').text();
	var keypad_window_string_length=keypad_window_string.length;
	keypad_window_new_string=keypad_window_string.substr(0,keypad_window_string_length-1);
	$('#TB_ajaxContent .keypad_screen').text(keypad_window_new_string);
}	
</script>