$(document).on('click', '#open-about-button', function (e) {
	var href = $(this).attr('href');
	var lightbox = LightboxManager.ajax_form_from_href($(this).text(), href);

	e.preventDefault();
	return false;
});

// Open the License Information
function openLicenseInfo() {
	LightboxManager.ajax_form_from_href("License Information","/monitor/index.php/menu/license_info");
}
