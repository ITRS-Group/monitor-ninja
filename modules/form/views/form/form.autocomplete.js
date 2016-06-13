(function () {

	"use strict";

	var _autocomplete_timeout = null;
	var _autocomplete_key_index = 0;

	function autocomplete (tables, term, callback) {
		$.ajax(_site_domain + _index_page + '/autocomplete/autocomplete', {
			data: {
				tables: tables,
				term: term
			},
			success: callback,
			method: "GET",
		});
	}

	function autocomplete_update (field) {

		var input = field.find('input[type="text"]');
		var list = input.siblings('.nj-form-field-autocomplete-items');
		var table_hidden = input.siblings('input[type="hidden"]');
		var tables = input.parent('.nj-form-field-autocomplete').attr('data-autocomplete').split(',');
		var term = input.val();

		clearTimeout(_autocomplete_timeout);
		_autocomplete_timeout = setTimeout(function () {
			autocomplete(tables, term, function (data) {

				if (data.length === 1 && data[0].name === term) {
					table_hidden.val(data[0].table);
					list.hide();
					return;
				}

				var fragment = document.createDocumentFragment();
				var current_table;
				data.forEach(function (item, index) {

					if (current_table != item.table) {
						var title = document.createElement('li');
						title.className = 'nj-form-field-autocomplete-items-title';
						title.setAttribute('disabled', 'disabled');
						title.textContent = item.table;
						current_table = item.table;
						fragment.appendChild(title);
					}

					var item_element = document.createElement('li');
					item_element.className = 'nj-form-field-autocomplete-item';
					item_element.setAttribute('data-autocomplete-value', item.name);
					item_element.setAttribute('data-autocomplete-table', item.table);
					item_element.textContent = item.name;
					fragment.appendChild(item_element);

				});

				list.empty();
				list.append(fragment);
				list.show();

			});
		}, 150);

	}

	$(document).on('keyup', '.nj-form-field-autocomplete input[type="text"]', function (e) {
		if (e.key === 'ArrowDown' || e.keycode === 40 || e.key === 'ArrowUp' || e.keycode === 38) {
			e.preventDefault();
			return false;
		}
		autocomplete_update($(this).parent('.nj-form-field-autocomplete'));
		e.preventDefault();
		return false;
	});

	$(document).on('keypress', '.nj-form-field-autocomplete input[type="text"]', function (e) {

		var input = $(this);
		var autocomplete = input.parent('.nj-form-field-autocomplete');
		var list = autocomplete.find('.nj-form-field-autocomplete-items');
		var table_hidden = autocomplete.find('input[type="hidden"]');
		var items = list.children('.nj-form-field-autocomplete-item');

		if (e.key === 'Enter' || e.keycode === 13) {

			var item = items.eq(_autocomplete_key_index - 1);
			table_hidden.val(item.attr('data-autocomplete-table'));
			input.val(item.attr('data-autocomplete-value'));

			list.hide();

			e.preventDefault();
			return false;

		} else if (e.key === 'ArrowDown' || e.keycode === 40) {

			_autocomplete_key_index = (_autocomplete_key_index < items.length) ? _autocomplete_key_index + 1 : items.length;

			items.removeClass('nj-form-field-autocomplete-item-selected');
			items.eq(_autocomplete_key_index - 1).addClass('nj-form-field-autocomplete-item-selected');

		} else if (e.key === 'ArrowUp' || e.keycode === 38) {

			_autocomplete_key_index = (_autocomplete_key_index > 1) ? _autocomplete_key_index - 1 : 1;
			items.removeClass('nj-form-field-autocomplete-item-selected');
			items.eq(_autocomplete_key_index - 1).addClass('nj-form-field-autocomplete-item-selected');

		} else if (e.key === 'Tab' || e.keycode === 9) {

			var item = items.eq(_autocomplete_key_index - 1);
			input.val(item.attr('data-autocomplete-value'));
			table_hidden.val(item.attr('data-autocomplete-table'));

			list.hide();

			e.preventDefault();
			return false;

		}
	});

	$(document).on('mousedown', '.nj-form-field-autocomplete-items li.nj-form-field-autocomplete-item', function (e) {

		var item = $(this);
		var list = item.parent('.nj-form-field-autocomplete-items');
		var input = list.siblings('input[type="text"]');
		var table_hidden = input.siblings('input[type="hidden"]');

		input.val(item.attr('data-autocomplete-value'));
		table_hidden.val(item.attr('data-autocomplete-table'));
		list.hide();

		e.preventDefault();
		return false;

	});

	$(document).on('focus', '.nj-form-field-autocomplete input[type="text"]', function (e) {
		autocomplete_update($(this).parent('.nj-form-field-autocomplete'));
	});

	$(document).on('blur', '.nj-form-field-autocomplete input[type="text"]', function (e) {
		var input = $(this);
		var autocomplete = input.parent('.nj-form-field-autocomplete');
		var list = autocomplete.find('.nj-form-field-autocomplete-items');
		list.hide();
		e.preventDefault();
		return false;
	});

}());
