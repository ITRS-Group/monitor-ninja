var lsfilter_textarea = {
	// Configuration

	// External methods
	init: function(element, orderelement)
	{
		var self = this; // To be able to access it from within handlers

		this.element = element;
		this.orderelement = orderelement;
		this.element.on('keyup paste cut', function(evt)
		{
			var query = op5trim((self.element.val()).toString());
			lsfilter_main.update_delayed(query, 'textarea');
		});
	},
	on: {
		'update_failed': function() {
			this.element.css("border", "2px solid #f40");
		},
		'update_ok': function(data) {
			if (!this.element)
				return;
			this.element.css("border", "2px solid #5d2");
			if (data.source == 'textarea') return;
			this.element.val(data.query);
		}
	},
	load: function()
	{
		var query = this.element.val();
		var order = this.orderelement.val();

		lsfilter_main.update(query, 'textarea', order);
	},

	// Internal veriables
	element: false,
	orderelement: false,
};
