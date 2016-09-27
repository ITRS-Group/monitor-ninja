FormModule.register(function (form) {
	form.find('.flatpicker').each(function (index, field) {
		$(field).flatpickr({
			'enableTime': true,
			'enableSeconds': true,
			'minDate': 'today',
			'time_24hr': true
		});
	});
});

