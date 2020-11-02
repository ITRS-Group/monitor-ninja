$(document).on('click', '#open-about-button', function (e) {
	var href = $(this).attr('href');
	var lightbox = LightboxManager.ajax_form_from_href($(this).text(), href);

	e.preventDefault();
	return false;
});