function tb_show(content) {//function called when the user clicks on a thickbox link
	$("body").append("<div id='TB_overlay' onclick='tb_remove();'></div><div id='TB_window'><div id='TB_ajaxContent'>"+content+"</div></div>");
	$("#TB_overlay").addClass("TB_overlayBG");//use background and opacity
	$("#TB_window").css({display:"block"});
	$("#TB_ajaxContent").center();
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