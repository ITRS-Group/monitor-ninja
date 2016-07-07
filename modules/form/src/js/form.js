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

    doc.on('autocompleted', '.nj-form-field input', function(e) {
        var field = $(this);
        var form = field.closest('form');
        var field_name = field.attr('name');
        // trim away possible array values, because if we're listening
        // for "host", we don't want to rely on specific rendering
        // details such as host[value], in the case of ORMObject form
        // fields
        field_name = field_name.replace(/\[.*/, "");
        // only handling <select/> elements for now
        form.find(".nj-form-field select[data-njform-target='"+field_name+"']")
            .each(function() {
                var select = $(this);
                select.empty();
                $.ajax(_site_domain + _index_page + '/form/perf_data_sources', {
                    data: {
                        table: form.find('input[name="'+field_name+'[table]"]').val(),
                        key: field.val()
                    },
                    success: function(data) {
                        if(!data.result.length) {
                            // TODO the css of Notify.message() is borked on TAC
                            Notify.message('No performance data available for this object');
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

    /* Range handling */
    // TODO this does not work for dynamically created widgets
    doc.find('.nj-form-field-range-hover').hide();

    doc.on('mousemove', '.nj-form-field-range', function (e) {
        $(this).find('.nj-form-field-range-hover')
            .css({
                top: e.clientY + 'px',
                left: e.clientX + 'px'
            }).text($(this).find('input').val());
    });

    // TODO this does not work for dynamically created widgets
    doc.find('input[type="range"]').hover(function () {
        $(this).siblings('.nj-form-field-range-hover').show();
    }, function () {
        $(this).siblings('.nj-form-field-range-hover').hide();
    });

    //Cancel(Reset) Button Click Event
    doc.on('click', '.cancel', function (){
        var form = $(this).closest('form');
        setTimeout(function() {
            form.find('.nj-form-option').trigger('change');
        }, 0);
    });

		var form_plugins = [];
		var Form = {

			register: function (plugin)	{
				form_plugins.push(plugin);
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
