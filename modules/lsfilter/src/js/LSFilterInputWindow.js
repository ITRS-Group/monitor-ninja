$().ready(function() {
	$('.main-toolbar-buttons').on('click', '.filter-query-button a', function() {
		var button = $(this);
		if (!button.data('drop-down')) {
			return true;
		}
		var dropdown = $('#'+button.data('drop-down'));
		if (!dropdown.length) {
			console.error("Could not find referenced "+button.data('drop-down'));
			return;
		}
		$('.filter-query-dropdown:visible').not(dropdown).toggle(100,
			function() {$('.filter-query-button').not(button).removeClass('active');}
		);
		dropdown.toggle(100,
			function () {button.toggleClass('active');}
		);
		if(button.attr('href') === '#') {
			return false;
		}
	});
	$(document).on("mouseenter", "#filter_result table td a span.x16-add-comment", function() {
		$(this).removeAttr("title");
	});
});
