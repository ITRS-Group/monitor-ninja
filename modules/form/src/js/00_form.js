var FormModule = (function () {


	var form_plugins = [];
	var Form = {

		/**
		 * This function runs through form conditionals to see whether the should
		 * be displayed or not based on the current state of the form.
		 *
		 * @param jQuery<Form> form - The form element to update
		 */
		update: function (form) {

			form.find('.nj-form-conditional').each(function() {

				var elem = $(this);
				var form = elem.closest('form');
				var field = form.find("[name='" + elem.attr('data-njform-rel') + "']");
				var value = field.val();

				if (field.attr('type') === 'checkbox') {
					value = field.is(':checked');
				}

				/**
				 * Set data-hidden-required on fields that are conditionally hidden so
				 * that they do not affect the require logic of html5, but so that we
				 * can still set them back to required once the fields are displayed.
				 */
					if (elem.attr('data-njform-value') == value) {
					elem.find('[data-hidden-required]')
					.attr('data-hidden-required', null)
					.attr('required', 'required');
					elem.show();
					} else {
						elem.find('[required]')
							.attr('required', null)
							.attr('data-hidden-required', 'required');
						elem.hide();
					}
			});

		},

		/**
		 * This function registers a new plugin to the form
		 *
		 * @param function plugin - Function to execute on new forms add, this
		 * 													recieves the form element as a parameter
		 */
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

		/**
		 * Adds a new form to the FormModule, all new nj-forms should be added
		 *
		 * @param jQuery<Form> form
		 */
		add_form: function (form_element) {

			form_plugins.forEach(function (plugin) {
				plugin(form_element);
			});

			Form.update(form_element);

		}

	}

	/**
	 * Respond to all changes in a nj-form by running the
	 * update function.
	 */
	$(document).on('change', '.nj-form', function () {
		Form.update($(this));
	});

	return Form;

})();
