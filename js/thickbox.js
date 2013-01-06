function tb_show(content) {
	$("#TB_window,#TB_overlay").remove();
	$("body").append("<div id='TB_overlay' onclick='tb_remove();'></div><div id='TB_window'><div id='prompt'>"+content+"</div></div>");
	$("#TB_overlay").addClass("TB_overlayBG");
	$("#TB_window").css({display:"block"});
	$("#prompt").center();
}

function tb_remove() {
	$("#TB_window").fadeOut("fast",function(){
	$('#TB_window').empty();
	$('#TB_overlay,#TB_window').remove();
	});
	$('input:text:first').focus();
}