<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="extra_toolbar">
	<div class="right lsfilter-edit-bar">
		<input type="hidden" id="server_name"
			value="<?php echo $_SERVER['SERVER_NAME']; ?>" /> <span
			id="filter_visual_result"></span>
		<button id="show-filter-query-builder-manual-button" title="Show/Edit Text Filter"><span class="icon-16 x16-edit"></span></button>
		<button id="show-filter-query-builder-graphical-button" title="Show/Edit Graphical Filter"><span class="icon-16 x16-command"></span></button>
		<button id="show-filter-query-saved" title="Show saved filters"><span class="icon-16 x16-status-detail"></span></button>
	</div>
	<div id="filter_result_totals"></div>
</div>

<div class="extra_toolbar_spacer"></div>

<div id="filter-query-builder">

	<div style="margin: 8px 0 0 8px">
		<button id="lsfilter_save_filter">Save Filter</button>
		<input type="checkbox" id="lsfilter_save_filter_global" /> Make global
	</div>

	<div id="filter-query-saved">

		<h2>Saved filters</h2>

		<ul id="filter-query-saved-filters">
		<ul>

	</div>

	<div id="filter-query-builder-manual">

		<h2>Manual input</h2>

		<form action="#" onsubmit="dosubmit();">
			<textarea style="width: 98%; height: 30px" name="filter_query"
				id="filter_query">
				<?php echo htmlentities($query); ?>
			</textarea>
		</form>

	</div>

	<div id="filter-query-builder-graphical">

		<h2>Graphical input</h2>

		<form id="filter_visual_form">
			<div id="filter_visual">Filter</div>
		</form>

	</div>
</div>

<div class="clear" id="filter_result"><div style="text-align: center; margin: 32px;"><span class="lsfilter-loader">Loading...</span></div></div>
