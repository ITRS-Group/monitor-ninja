
$().ready(function() {
	var hide_main_box = function () {
		if ($('#filter-query-builder-manual').css('display') == 'none' && 
			$('#filter-query-saved').css('display') == 'none' &&
			$('#filter-query-multi-action').css('display') == 'none') {
			
			$('#filter-query-builder').hide();

		}
	}

	$('#show-filter-query-saved').click(function () {
		$('#filter-query-saved').toggle(100, function () {
			switch ($(this).css('display')) {
				case "block":
					$('#show-filter-query-saved').css('background', 'rgba(0,0,0,0.1)');
					$('#filter-query-builder').show(200);
					break;
				case "none":
					$('#show-filter-query-saved').css('background', 'transparent');
					hide_main_box();
					break;
			}
		});
	});

	$('#show-filter-query-builder-manual-button').click(function () {
		$('#filter-query-builder-manual').toggle(100, function () {
			switch ($(this).css('display')) {
				case "block":
					$('#show-filter-query-builder-manual-button').css('background', 'rgba(0,0,0,0.1)');
					$('#filter-query-builder').show(200);
					break;
				case "none":
					$('#show-filter-query-builder-manual-button').css('background', 'transparent');
					hide_main_box();
					break;
			}
		});
	});

	$('#show-filter-query-multi-action').click(function () {
		$('#filter-query-multi-action').toggle(100, function () {
			switch ($(this).css('display')) {
				case "block":
					$('#show-filter-query-multi-action').css('background', 'rgba(0,0,0,0.1)');
					$('#filter-query-builder').show(200);
					break;
				case "none":
					$('#show-filter-query-multi-action').css('background', 'transparent');
					hide_main_box();
					break;
			}
		});
	});
});
