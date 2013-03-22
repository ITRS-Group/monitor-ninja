$().ready(function() {
	$('#show-filter-query-builder-button').click(function () {
		$('#filter-query-multi-action:visible').toggle(100, function() {
			$('#show-filter-query-multi-action').removeClass('active');
		});
		$('#filter-query-builder').toggle(100, function () {
			switch ($(this).css('display')) {
				case "block":
					$('#show-filter-query-builder-button').addClass('active');
					$('#filter-query-builder').show(200);
					break;
				case "none":
					$('#show-filter-query-builder-button').removeClass('active');
					break;
			}
		});
	});

	$('#show-filter-query-multi-action').click(function () {
		$('#filter-query-builder:visible').toggle(100, function() {
			$('#show-filter-query-builder-button').removeClass('active');
		});
		$('#filter-query-multi-action').toggle(100, function () {
			switch ($(this).css('display')) {
				case "block":
					$('#show-filter-query-multi-action').addClass('active');
					break;
				case "none":
					$('#show-filter-query-multi-action').removeClass('active');
					break;
			}
		});
	});
});
