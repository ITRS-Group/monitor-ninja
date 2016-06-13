$(function() {

	$(document).on('change', '.nj-form-option', function() {

		var field = $(this);
		var name = field.attr('name');
		var value = field.val();
		var form = field.closest('form');

		if (field.attr('type') === 'checkbox') {
			value = field.is(':checked');
		}

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

	$(document).find('.nj-form-conditional').each(function() {

		var elem = $(this);
		var form = elem.closest('form');
		var field = form.find("[name='" + elem.attr('data-njform-rel') + "']");
		var value = field.val();

		if (field.attr('type') === 'checkbox') {
			value = field.is(':checked');
		}

		if (elem.attr('data-njform-value') == value) {
			elem.show();
		} else {
			elem.hide();
		}
	});

	$(document).find('.nj-form-field-range-hover').hide();
	$(document).on('mousemove', '.nj-form-field-range', function (e) {
		$(this).find('.nj-form-field-range-hover')
			.css({
				top: e.clientY + 'px',
				left: e.clientX + 'px'
			}).text($(this).find('input').val());
	});

	$(document).find('input[type="range"]').hover(function () {
		$(this).siblings('.nj-form-field-range-hover').show();
	}, function () {
		$(this).siblings('.nj-form-field-range-hover').hide();
	});

	$(document).on('submit', '.nj-form', function() {

	});

});
