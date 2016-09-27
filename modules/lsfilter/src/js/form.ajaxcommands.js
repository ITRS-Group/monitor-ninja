(function () {

	"use strict";

	function open_dialog (title, href) {

		var lightbox = LightboxManager.ajax_form_from_href(title, href);

		window.location.hash = 'lightbox:' + encodeURIComponent(title) + ':' + encodeURIComponent(href);

		$(lightbox.node).one('click', 'input[type="reset"]', function () {
			lightbox.remove();
		});

		function submit_handler (e) {

			var data = new FormData(this);
			e.preventDefault();

			$.ajax({
				url: this.getAttribute('action'),
				data: data,
				processData: false,
				contentType: false,
				type: 'POST',
				complete: function (xhr) {

					var content = document.createElement('div');
					var responsebox = LightboxManager.create();

					content.innerHTML = xhr.responseText;
					responsebox.content(content);

					var header = content.querySelector('h1');
					responsebox.header(header);

					responsebox.button('Done', function () {
						responsebox.remove();
						setTimeout(function () {
							window.location.reload();
						}, 500);
					});

					lightbox.remove();
					responsebox.show();

				}
			});
		}

		$(lightbox.node).one('submit', 'form', submit_handler);

	}

	$(document).on('click', '.command-ajax-link', function (e) {

		var href = $(this).attr('href');
		var title = $(this).text();

		if (!title) {
			title = $(this).attr('title');
		}

		open_dialog(title, href);

		e.preventDefault();
		return false;

	});

	$(window).load(function () {

		var hash = window.location.hash;

		if (hash.match(/^\#lightbox/)) {

			hash = hash.split(':');
			hash.shift();

			var title = hash.shift();
			var href = hash.shift();

			open_dialog(
				decodeURIComponent(title),
				decodeURIComponent(href)
			);

		}
	})


})();
