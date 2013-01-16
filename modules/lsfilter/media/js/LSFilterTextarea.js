var lsfilter_textarea = {
	// Configuration
	
	// External methods
	update: function(data)
	{
		this.element.css("border", "2px solid #5d2"); // green
		if (data.source == 'textarea') return;
		this.element.val(data.query);
	},
	init: function()
	{
		var self = this; // To be able to access it from within handlers
		
		this.element = $('#filter_query');
		this.orderelement = $('#filter_query_order');
		this.element.bind('keyup paste cut', function(evt)
		{
			query = self.element.val();
			self.handle_propertychange(query);
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
	
	// Internal methods
	handle_propertychange: function(query)
	{
		// Set red until parsed...
		this.element.css("border", "2px solid #f40"); // red
		lsfilter_main.update_delayed(query, 'textarea');
	}
};
