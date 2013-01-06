<!-- keypad icon -->

<div id="keypad_icon">
	<table width="100%" cellpadding="3" title="selfcheck_button">
	    <tr>
	    	<td onclick="show_keypad()" title="selfcheck_button">
	    		<p>manually</p>
	    		<p>enter</p>
	    		<p>your id</p>
	    	</td>
	    	<td onclick="show_keypad()" style="width:5px;" title="selfcheck_button"><img src="images/keypad_icon.gif"/></td>
	    </tr>
	</table>
</div>

<!-- end keypad icon -->

<!-- keypad -->

<div id="keypad_container">
	<table class="keypad">
		<tr>
			<td colspan="3" class="keypad_screen"></td>
		</tr>
		<tr>
			<td><div data-val="1" title="selfcheck_button">1</div></td>
			<td><div data-val="2" title="selfcheck_button">2</div></td>
			<td><div data-val="3" title="selfcheck_button">3</div></td>
		</tr>
		<tr>
			<td><div data-val="4" title="selfcheck_button">4</div></td>
			<td><div data-val="5" title="selfcheck_button">5</div></td>
			<td><div data-val="6" title="selfcheck_button">6</div></td>
		</tr>
		<tr>
			<td><div data-val="7" title="selfcheck_button">7</div></td>
			<td><div data-val="8" title="selfcheck_button">8</div></td>
			<td><div data-val="9" title="selfcheck_button">9</div></td>
		</tr>
		<tr>
			<td onclick="delete_keypad_entry();"><div title="selfcheck_button"><span>delete</span></div></td>
			<td><div data-val="0" title="selfcheck_button">0</div></td>
			<td><div title="selfcheck_button"><span onclick="tb_remove();">cancel</span></div></td>
		</tr>
	</table>
	<div onclick="$('#barcode').val($('#prompt .keypad_screen').text());$('#form').submit();" class="ok_button button" title="selfcheck_button" style="width:100%;">
		<h1>OK</h1>
	</div>
</div>

<!-- end keypad -->

<script type="text/javascript">
//keypad functions
function show_keypad(){
	tb_remove();
	tb_show($('#keypad_container').html());
	var keypad_key=$('#prompt .keypad div');
	keypad_key.click(function (){
		if (typeof $(this).data('val')!= 'undefined'){
			var keypad_window=$('#prompt .keypad_screen');
			if (keypad_window.text().length<19){
				keypad_window.append($(this).data('val'));
			}
		}
	});
}

function delete_keypad_entry(){
	var keypad_window_string=$('#prompt .keypad_screen').text();
	var keypad_window_string_length=keypad_window_string.length;
	keypad_window_new_string=keypad_window_string.substr(0,keypad_window_string_length-1);
	$('#prompt .keypad_screen').text(keypad_window_new_string);
}	
</script>