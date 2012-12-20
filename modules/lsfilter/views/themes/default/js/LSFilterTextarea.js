var lsfilter_textarea = {
	// Configuration

	// External methods
	update : function(query, source, metadata) {
		this.element.css("border", "2px solid #5d2"); // green
		if( source == 'textarea' ) return;
		this.element.val(query);
	},
	init : function() {
		var self = this; // To be able to access it from within handlers

		this.element = $('#filter_query');
		this.element.bind('input propertychange', function(evt) {
			query = self.element.val();
			self.handle_propertychange(query);
		});
	},
	load : function() {
		query = this.element.val();
		this.element.css("border", "2px solid #f40"); // red
		lsfilter_main.update(query, 'textarea');
	},

	// Internal veriables
	element : false,

	// Internal methods
	handle_propertychange : function(query) {
		// Set red until parsed...
		this.element.css("border", "2px solid #f40"); // red
		lsfilter_main.update_delayed(query, 'textarea');
	}
};