function adjust_height() {
	document.getElementById('iframe').style.height = parseInt(document.documentElement.clientHeight-67)+'px';
}

window.onload = function(){
 adjust_height();
 collapse_menu('');
}