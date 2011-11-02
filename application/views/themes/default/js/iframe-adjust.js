$(document).ready(function() {
	adjust_height();
	collapse_menu('');
});

function adjust_height() {
	var new_height = parseInt(document.documentElement.clientHeight) - 68;
	if(new_height > 500) {
		$('#iframe').css('height', new_height+'px');
		$('#nagvis').css('height', new_height+'px');
		$('body').css('overflow-y', 'hidden');
	} else {
		$('body').css('overflow-y', 'scroll');
	}
};

window.onload = function(){
	adjust_height();
	collapse_menu('');
};

window.onresize = function (){
	adjust_height();
};
