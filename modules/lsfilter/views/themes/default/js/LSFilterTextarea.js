var lsfilter_textarea = {
	// Configuration

	// External methods
	update : function(query) {
		this.element.css("border", "2px solid #5d2"); // green
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

	// Internal veriables
	element : false,

	// Internal methods
	handle_propertychange : function(query) {
		try {
			var parser = new LSFilter(new LSFilterPreprocessor(),
					new LSFilterMetadataVisitor());
			var metadata = parser.parse(query);

			this.element.css("border", "2px solid #5d2"); // green
			lsfilter_main.update(query, 'textarea');
		} catch (ex) {
			this.element.css("border", "2px solid #f40"); // red
		}
	}
};