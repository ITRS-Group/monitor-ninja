$(document).ready(function () {
	$('#iframe').load(function() {
		$(this).focus();
		$(this.contentDocument).find('.ui-widget-header tr').each(function () {
			$(this).append('<td align="right"><a href="#" class="default" title="Make default graph" alt="Make default graph"><img src="' + _site_domain + '/application/views/themes/default/icons/16x16/shield-ok.png"/></a></td>');
			$(this).find('.default').click(function() {
				var src = $(this).closest('div').next('div').find('img').attr('src');
				var match = src.match(/\?(.*)&view=(\d)&source=(\d)/);
				var param = match[1];
				var view = match[2];
				var source = match[3];
				$.post(_site_domain + _index_page + '/ajax/pnp_default/', {
					param: param,
					view: view,
					source: source
				}, function () {
					$.jGrowl(_settings_msg, {header: _success_header});
				});
				return false;
			});
		});
	});
});
