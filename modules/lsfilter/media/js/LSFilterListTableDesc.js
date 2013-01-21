function lsfilter_list_table_desc(metadata, columndesc)
{
	this.metadata = metadata;
	this.vis_columns = [];
	this.col_renderers = [];
	this.db_columns = [];
	
	if (!listview_renderer_table[metadata.table]) return;
	
	this.col_renderers = listview_renderer_table[metadata.table];
	
	var all_columns = [];
	for ( var col in this.col_renderers) {
		all_columns.push(col);
	}
	
	if (columndesc) {
		// TODO: handling of column slection description
	}
	else {
		this.vis_columns = all_columns;
	}
	
	/* Fetch database columns */
	for ( var i = 0; i < this.vis_columns.length; i++) {
		var column_obj = this.col_renderers[this.vis_columns[i]];
		for ( var j = 0; j < column_obj.depends.length; j++) {
			this.db_columns.push(column_obj.depends[j]);
		}
	}

	/* Build fetch sort columns method */
	this.sort_cols = function(vis_col)
	{
		var sort = this.col_renderers[vis_col].sort;
		if (sort) return sort;
		return [];
	}
}
