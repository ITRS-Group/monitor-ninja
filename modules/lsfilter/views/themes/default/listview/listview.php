<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div class="right">
	<input type="hidden" id="server_name" value="<?php echo $_SERVER['SERVER_NAME']; ?>" />
	<span id="filter_visual_result"></span>
	<button id="show-filter-query-builder-manual-button">Text Query Builder</button>
	<button id="show-filter-query-builder-graphical-button">Graphical Query Builder</button>
</div>
<div class="clear"></div>

<div id="filter-query-builder-manual">	

	<h2>Manual input</h2>

	<form action="#" onsubmit="dosubmit();">
		<textarea style="width: 98%; height: 30px" name="filter_query" id="filter_query"><?php echo htmlentities($query); ?></textarea>
	</form>

</div>

<div id="filter-query-builder-graphical">

	<h2>Graphical input</h2>

	<form id="filter_visual_form">
		<div id="filter_visual">Filter</div>
	</form>

</div>

<div class="clear" id="filter_result">Result</div>