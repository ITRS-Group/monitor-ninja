function adjust_height() {
	$('#iframe').css('height', parseInt(document.documentElement.clientHeight-67)+'px');
	$('#nagvis').css('height', parseInt(document.documentElement.clientHeight-67)+'px');
	$('body').css('overflow-y', 'hidden');
}

window.onload = function(){
 adjust_height();
 collapse_menu('');
}

window.onresize = function (){
 adjust_height();
}