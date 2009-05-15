$(document).ready(function() {
	$("#host_table").tablesorter({
		sortList: [[1,0]],
		headers: {
			6: { sorter: false }
		},
	}),
	$("#service_table").tablesorter({
		sortList: [[1,0]],
		headers: {
			7: { sorter: false }
		},
	}),
	// group overview tables
	$(".group_overview_table").tablesorter({
		sortList: [[1,0]],
		headers: {
			2: { sorter: false },
			3: { sorter: false }
		},
	}),	
	$(".group_grid_table").tablesorter({
		sortList: [[0,0]],
		headers: {
			1: { sorter: false },
			2: { sorter: false }
		},
	}),
	$("#group_summary_table").tablesorter({
		sortList: [[0,0]],
		headers: {
			1: { sorter: false },
			2: { sorter: false }
		},
	}),
	$(".comments_table").tablesorter({
		sortList: [[0,0]],
		headers: {
			7: { sorter: false }
		},
	});
});