
(function () {

	function Search (root) {

		var self = this;

		this.box = root;
		this.form = root.find('form');
		this.input = root.find('input');
		this.complete = root.find('.autocomplete');
		this.request;

		this.selected;
		this.index = -1;

		this.form.on('submit', this.submithandler.bind(this));
		this.input.on('blur', this.blurhandler.bind(this));
		this.input.on('keyup', this.keyhandler.bind(this));

	};

	Search.prototype = {

		render: function (results) {

			var tables = Object.keys(results);
			var count = 0;
			var object;

			this.index = -1;

			for (var t = 0; t < tables.length; t++) {
				for (var o = 0; o < results[tables[t]].length; o++) {

					count++;
					object = results[tables[t]][o];

					if (count < 10) {
						this.complete.append(
							$('<li>').append(
								$('<a>')
									.attr('href', _site_domain + _index_page + object.link)
									.text(object.key)
						));
					}

				}
			}

			if (count > 0) {
				this.complete.show();
			}

		},

		autocomplete: function (query) {

			var encoded = encodeURIComponent(query);
			var self = this;

			this.request = $.get(_site_domain + _index_page + '/search/autocomplete?query=' + encoded, function (data) {
				self.render(data);
			}, 'json').fail(function (e) {
				console.log(e);
			});

		},

		submithandler: function (e) {

			if (this.selected) {
				e.preventDefault();
				this.selected.find('a').get(0).click();
			}

		},

		keyhandler: function (e) {

			if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {

				this.index = (e.key === 'ArrowUp') ? this.index - 1 : this.index + 1;

				if (this.index < 0) {
					if (this.selected) this.selected.removeClass('autocomplete-selected');
					this.index = -1;
					this.selected = null;
				} else if (this.index >= this.complete.children('li').length) {
					this.index--;
				} else {
					if (this.selected) this.selected.removeClass('autocomplete-selected');
					this.selected = this.complete.children('li:eq(' + this.index + ')');
					this.selected.addClass('autocomplete-selected');
				}

			} else if (e.keyCode >= 48) {

				this.selected = null;
				this.complete.hide();
				this.complete.empty();

				if (this.request) {
					this.request.abort();
					this.request = null;
				}

				var query = this.input.val();
				if (query.length >= 3) {
					this.autocomplete(query);
				}

			}

		},

		blurhandler: function (e) {

			this.complete.hide();
			this.complete.empty();
			this.selected = null;

		}

	};

	$(document).ready(function () {
		$('.search').each(function (i, element) {
			new Search($(element));
		});
	});

})();