$(function() {
	$(document).on('change', '.nj-form-option', function() {
		var name = $(this).attr('name');
		var value = $(this).val();
		var form = $(this).closest('form');

		form.find('.nj-form-conditional').each(function() {
			var elem = $(this);
			if (elem.attr('data-njform-rel') == name) {
				if (elem.attr('data-njform-value') == value) {
					elem.show();
				} else {
					elem.hide();
				}
			}
		});
	});

	$(document).on('submit', '.nj-form', function() {

	});

	$(document).find('.nj-form-conditional').each(function() {
		var elem = $(this);
		var form = elem.closest('form');
		var relelem = form.find("[name='" + elem.attr('data-njform-rel') + "']");
		if (elem.attr('data-njform-value') == relelem.val()) {
			elem.show();
		} else {
			elem.hide();
		}
	});
});
