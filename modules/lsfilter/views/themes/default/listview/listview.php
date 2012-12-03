<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<style type="text/css">
.resultvisual {
	margin-left: 2em;
}
</style>
<form action="#" onsubmit="dosubmit();">
<textarea name="filter_query" id="filter_query"><?php echo htmlentities($query); ?></textarea>
<input type="submit" value="Search">
</form>
<div id="filter_visual">Filter</div>
<div id="filter_result">Result</div>