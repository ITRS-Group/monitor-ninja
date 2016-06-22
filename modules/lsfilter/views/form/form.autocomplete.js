(function () {

	"use strict";

	var _document = $(document);
	var Autocomplete = function (form_field) {

		this.key_index = 1;
		this.timeout = null;

		this.input = form_field.find('.nj-form-field-autocomplete-input');
		this.shadow = form_field.find('.nj-form-field-autocomplete-shadow');
		this.items = form_field.find('.nj-form-field-autocomplete-items');
		this.dropper = form_field.find('.nj-form-field-autocomplete-dropper');
		this.table_element = form_field.find('.nj-form-field-autocomplete-table');
		this.placeholder = this.input.attr('placeholder');

		this.tables = form_field.attr('data-autocomplete').split(',');

		this.input.on('keyup', function (e) {
			e.preventDefault();
			if (e.key !== 'ArrowDown' && e.keycode !== 40 && e.key !== 'ArrowUp' && e.keycode !== 38) {
				this.update(this.input.val());
			}
			return false;
		}.bind(this));

		this.input.on('keypress', function (e) {

			this.set_shadow("");
			if (e.key === 'Enter' || e.keycode === 13) {

				this.set_value(
					this.get_current().attr('data-autocomplete-value'),
					this.get_current().attr('data-autocomplete-table')
				);
				this.hide();

				e.preventDefault();
				return false;

			} else if (e.key === 'ArrowDown' || e.keycode === 40) {

				var items = this.items.children('li.nj-form-field-autocomplete-item');
				this.key_index = (this.key_index < items.length) ? this.key_index + 1 : items.length;
				this.reselect();

			} else if (e.key === 'ArrowUp' || e.keycode === 38) {

				var items = this.items.children('li.nj-form-field-autocomplete-item');
				this.key_index = (this.key_index > 1) ? this.key_index - 1 : 1;
				this.reselect();

			} else if (e.key === 'Tab' || e.keycode === 9) {

				this.set_value(
					this.get_current().attr('data-autocomplete-value'),
					this.get_current().attr('data-autocomplete-table')
				);

				this.hide();

				e.preventDefault();
				return false;

			}

		}.bind(this));

		this.items.on('mousedown', 'li.nj-form-field-autocomplete-item', function (e) {

			var item = $(e.target);
			this.set_value(
				item.attr('data-autocomplete-value'),
				item.attr('data-autocomplete-table')
			);

			this.hide();

			e.preventDefault();
			return false;

		}.bind(this));

		this.dropper.on('click', function (e) {
			e.preventDefault();
			if(this.items.is(':visible')) {
				this.items.hide();
			} else {
				this.update('');
			}
			return false;
		}.bind(this));

		this.input.on('focus', function (e) {
			this.update(this.input.val());
			this.input.attr('placeholder', "");
		}.bind(this));

		this.input.on('blur', function (e) {
			this.hide();
			this.input.attr('placeholder', this.placeholder);
			e.preventDefault();
			return false;
		}.bind(this));

	};

	Autocomplete.prototype = {

		get_current: function () {
			return this.items
				.children('li.nj-form-field-autocomplete-item')
				.eq(this.key_index - 1);
		},

		set_value: function (value, table) {
			if(value === this.input.val()) {
				return;
			}
			this.input.val(value);
			// make sure that those listening on events don't get
			// left in the cold
			this.input.trigger('autocompleted');
			this.table_element.val(table);
			this.set_shadow(value);
		},

		set_shadow: function (value) {
			this.shadow.val(value);
		},

		reselect: function () {

			var items = this.items.children('.nj-form-field-autocomplete-item');
			var item = items.eq(this.key_index - 1);

			items.removeClass('nj-form-field-autocomplete-item-selected');
			item.addClass('nj-form-field-autocomplete-item-selected');
			this.shadow.val(item.attr('data-autocomplete-value'));

		},

		fetch: function (term, callback) {

			$.ajax(_site_domain + _index_page + '/form/autocomplete', {
				data: {
					tables: this.tables,
					term: term
				},
				success: callback,
				method: "GET",
			});

		},

		update: function (term) {

			clearTimeout(this.timeout);
			this.timeout = setTimeout(function () {
				this.fetch(term, function (data) {

					if (data.length === 1 && data[0].name === term) {
						this.table_element.val(data[0].table);
						this.hide();
						return;
					}


					this.key_index = 1;
					if (term.length > 0)
						this.shadow.val(data[0].name);

					this.items.empty();
					this.populate(data);
					this.items.show();

				}.bind(this));
			}.bind(this), 150);

		},

		populate: function (key_values) {

			var fragment = document.createDocumentFragment();
			var current_table;

			key_values.forEach(function (item, index) {

				if (current_table != item.table) {
					var title = $('<li class="nj-form-field-autocomplete-items-title">').text("Suggestions");
					fragment.appendChild(title.get(0));
					current_table = item.table;
				}

				var item_element = $('<li class="nj-form-field-autocomplete-item">').attr({
					'data-autocomplete-value': item.key,
					'data-autocomplete-table': item.table
				}).text(item.name);

				fragment.appendChild(item_element.get(0));

			});

			this.items.append(fragment);

		},

		hide: function () {
			this.items.hide();
			this.items.empty();
		},

		show: function () {
			this.items.show();
		}

	};


	_document.ready(function () {
		_document.find('.nj-form-field-autocomplete').each(function () {
			new Autocomplete($(this));
		});
	});

}());
