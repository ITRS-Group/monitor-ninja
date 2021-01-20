(function () {

	"use strict";

	/**
	 * This should not be here, but instead have its own module
	 * but it is so tightly knit into the autocomplete currently
	 * that  I don't want to touch it.
	 */
	$(document).on('autocompleted', '.nj-form-field input', function(e) {
		var field = $(this);
		var form = field.closest('form');
		var field_name = field.attr('name');
		/* trim away possible array values, because if we're listening
		 * for "host", we don't want to rely on specific rendering
		 * details such as host[value], in the case of ORMObject form
		 * fields */
			field_name = field_name.replace(/\[.*/, "");
		/* only handling <select/> elements for now */

		form.find(".nj-form-field select[data-njform-target='"+field_name+"']").each(function() {
			var select = $(this);
			select.empty();
			$.ajax(_site_domain + _index_page + '/perfdata/perf_data_sources', {
				data: {
					table: form.find('input[name="'+field_name+'[table]"]').val(),
					key: field.val()
				},
				success: function(data) {
					if(!data.result.length) {
						Notify.message('No performance data available for this object', {
							type: "info"
						});
						return;
					}
					$.each(data.result, function(key, value) {
						select.append($("<option/>").text(value).val(value));
					});
				},
				error: function(jqXHR) {
					var msg = '';
					try {
						msg = JSON.parse(jqXHR.responseText).message;
					} catch (e) {
						msg = 'Something went wrong, perhaps you could try again';
					}
					Notify.message(msg, {'type': 'error'});
				},
				method: "GET"
			});

		});

	});


	var Autocomplete = function (form_field) {

		this.key_index = 1;
		this.timeout = null;

		this.input = form_field.find('.nj-form-field-autocomplete-input');
		this.items = form_field.find('.nj-form-field-autocomplete-items');
		this.dropper = form_field.find('.nj-form-field-autocomplete-dropper');
		this.table_element = form_field.find('.nj-form-field-autocomplete-table');
		this.placeholder = this.input.attr('placeholder');

		this.tables = form_field.attr('data-autocomplete').split(',');
		var self = this;

		this.input.on('keyup', function (e) {
			e.preventDefault();
			if (e.key !== 'ArrowDown' && e.keyCode !== 40 && e.key !== 'ArrowUp' && e.keyCode !== 38) {
				self.update(self.input.val());
			}
			return false;
		});

		this.input.on('keydown', function (e) {

			if (e.key === 'Enter' || e.keyCode === 13) {

				self.set_value(
					self.get_current().attr('data-autocomplete-value'),
					self.get_current().attr('data-autocomplete-table')
				);
				self.hide();

				e.preventDefault();
				return false;

			} else if (e.key === 'ArrowDown' || e.keyCode === 40) {

				var items = self.items.children('li.nj-form-field-autocomplete-item');
				self.key_index = (self.key_index < items.length) ? self.key_index + 1 : items.length;
				self.reselect();

			} else if (e.key === 'ArrowUp' || e.keyCode === 38) {

				self.key_index = (self.key_index > 1) ? self.key_index - 1 : 1;
				self.reselect();

			} else if (e.key === 'Tab' || e.keyCode === 9) {

				self.set_value(
					self.get_current().attr('data-autocomplete-value'),
					self.get_current().attr('data-autocomplete-table')
				);

				self.hide();

				e.preventDefault();
				return false;

			}

		});

		this.items.on('mousedown', 'li.nj-form-field-autocomplete-item', function (e) {

			var item = $(e.target);
			self.set_value(
				item.attr('data-autocomplete-value'),
				item.attr('data-autocomplete-table')
			);

			self.hide();

			e.preventDefault();
			return false;

		});

		this.input.on('focus', function (e) {
			self.update(self.input.val());
			self.input.attr('placeholder', "");
		});

		this.input.on('blur', function (e) {
			self.hide();
			self.input.attr('placeholder', self.placeholder);
			e.preventDefault();
			return false;
		});

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
		},

		reselect: function () {

			var items = this.items.children('.nj-form-field-autocomplete-item');
			var item = items.eq(this.key_index - 1);

			items.removeClass('nj-form-field-autocomplete-item-selected');
			item.addClass('nj-form-field-autocomplete-item-selected');

		},

		fetch: function (term, callback) {

			$.ajax(_site_domain + _index_page + '/autocomplete/autocomplete', {
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

					this.items.empty();
					this.populate(data);
					this.items.show();

				}.on(this));
			}.on(this), 150);

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

	FormModule.register(function (form) {
		form.find('.nj-form-field-autocomplete').each(function (index, field) {
			new Autocomplete($(field));
		});
	});

}());
