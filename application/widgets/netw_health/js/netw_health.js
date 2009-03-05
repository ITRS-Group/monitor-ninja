$(document).ready(function() {
	//doBlink('#widget-network_health', 1, 4);
	$('#widget-network_health').toggle('drop');
	$('#widget-network_health').toggle('drop');
});
var doBlink = function(obj,start,finish) { jQuery(obj).fadeOut(300).fadeIn(300); if(start!=finish) { start=start+1; doBlink(obj,start,finish); } }
