$(document).ready(function() {
	$(document).on("click", "#open-about-button", function(ev) {
		ev.preventDefault();
		var link = $(this);
		LightboxManager.html_from_ajax(link.text(), link.attr("href"));
	});
});
