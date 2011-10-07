$(document).ready(function() {
	adjust_height();
	collapse_menu('');
});

function adjust_height() {
	$('#iframe').css('height', parseInt(document.documentElement.clientHeight-68)+'px');
	$('#nagvis').css('height', parseInt(document.documentElement.clientHeight-68)+'px');
	$('body').css('overflow-y', 'hidden');
}

window.onload = function(){
 adjust_height();
 collapse_menu('');
}

window.onresize = function (){
 adjust_height();
}