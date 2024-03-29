$(document).ready(function () {
	$('#iframe').on("load", function() {
		$(this).focus();
		$(this.contentDocument).find('.ui-widget-header tr').each(function () {
			$(this).append('<td align="right"><a href="#" class="default" title="Make default graph" alt="Make default graph"><img src="' + _site_domain + '/application/views/icons/x16/shield-ok.png"/></a></td>');
			$(this).find('.default').click(function() {
				var src = $(this).closest('div').next('div').find('img').attr('src');
				var match = src.match(/\?(.*)&source=(\d)&view=(\d)/);
				$.ajax(
					_site_domain + _index_page + '/pnp/pnp_default/',
					{
						data: {
							param: match[1],
							source: match[2],
							view: match[3],
							csrf_token: _csrf_token
						},
						success: function () {
							Notify.message(_settings_msg, {type: "error"});
						},
						error: function () {
							Notify.message(_error_header, {type: "error"});
						},
						type: 'POST'
					}
				);
				return false;
			});
		});
	});
});
