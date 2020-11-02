function getUrlParam(name, input_url_param) {
	var name = name.replace('[', '\\[').replace(']', '\\]');
	var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
	var results = regex.exec(input_url_param?input_url_param:window.location);
	if(results === null) {
		return '';
	} else {
		return results[1];
	}
}
