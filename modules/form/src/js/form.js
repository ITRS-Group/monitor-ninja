var FormModule = (function () {

	var doc = $(document);

	doc.on('change', '.nj-form-option', function() {
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

	/* Range handling */
	doc.find('.nj-form-field-range-hover').hide();

	doc.on('mousemove', '.nj-form-field-range', function (e) {
		$(this).find('.nj-form-field-range-hover')
		.css({
			top: e.clientY + 'px',
			left: e.clientX + 'px'
		}).text($(this).find('input').val());
	});

	doc.find('input[type="range"]').hover(function () {
		$(this).siblings('.nj-form-field-range-hover').show();
	}, function () {
		$(this).siblings('.nj-form-field-range-hover').hide();
	});

	doc.on('click', '.cancel', function (){
		var form = $(this).closest('form');
		var editbox = form.closest('.widget-editbox');
		setTimeout(function() {
			form.find('.nj-form-option').trigger('change');
			//cancel button click event to show widget content
			editbox.hide();
			editbox.next('.widget-content').show();
		}, 0);
	});

	var form_plugins = [];
	var Form = {

		register: function (plugin) {
			if (form_plugins.indexOf(plugin) < 0) {
				if (typeof(plugin) === 'function') {
					form_plugins.push(plugin);
					return true;
				} else {
					console.log("Could not register plugin, plugin registrar must be a function.");
				}
			} else {
				console.log("Could not register plugin, plugin already registered.");
			}
			return false;
		},

		add_form: function (form_element) {

			form_element.find('.nj-form-conditional').each(function() {

				var elem = $(this);
				var form = elem.closest('form');
				var field = form.find("[name='" + elem.attr('data-njform-rel') + "']");
				var value = field.val();

				if (field.attr('type') === 'checkbox') {
					value = field.is(':checked');
				}

				if (elem.attr('data-njform-value') == value) elem.show();
				else elem.hide();

			});

			form_plugins.forEach(function (plugin) {
				plugin(form_element);
			});

		}

	}

	return Form;

})();
