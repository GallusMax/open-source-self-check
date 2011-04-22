function tb_show(content) {
	$("body").append("<div id='TB_overlay' onclick='tb_remove();'></div><div id='TB_window'><div id='prompt'>"+content+"</div></div>");
	$("#TB_overlay").addClass("TB_overlayBG");
	$("#TB_window").css({display:"block"});
	$("#prompt").center();
}

function tb_remove() {
	$("#TB_window").fadeOut("fast",function(){
	$('#TB_window').empty();
	$('#TB_overlay').remove();
	$('#TB_window').remove();
	$('#prompt_box').remove();
	});
	$('input:text:first').focus();
}