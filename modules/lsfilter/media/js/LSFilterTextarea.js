var lsfilter_textarea = {
	// Configuration

	// External methods
	update: function(data)
	{
		if (!this.element)
			return;
		this.element.css("border", "2px solid #5d2"); // green
		if (data.source == 'textarea') return;
		this.element.val(data.query);
		this.last_query = data.query;
	},
	init: function(element, orderelement)
	{
		var self = this; // To be able to access it from within handlers

		this.element = element;
		this.orderelement = orderelement;
		this.element.bind('keyup paste cut', function(evt)
		{
			var query = (self.element.val()).toString().trim();
			if(self.last_query === query) {
				// we don't want to update the view if we
				// aren't modifying the value at all (for
				// example when navigating the text or adding
				// whitespace)
				return;
			}
			// Set red until parsed...
			self.element.css("border", "2px solid #f40");
			lsfilter_main.update_delayed(query, 'textarea');
			self.last_query = query;
		});
	},
	load: function()
	{
		var query = this.element.val();
		var order = this.orderelement.val();

		this.element.css("border", "2px solid #f40"); // red
		lsfilter_main.update(query, 'textarea', order);
	},

	// Internal veriables
	element: false,
	orderelement: false,
};
