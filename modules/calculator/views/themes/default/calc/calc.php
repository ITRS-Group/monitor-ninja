<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<style type="text/css">
.resultvisual {
	margin-left: 2em;
}
</style>
<form action="#" onsubmit="dosubmit();">
<textarea name="calculator_query" id="calculator_query"><?php echo htmlentities($query); ?></textarea>
<input type="submit" value="Search">
</form>
<h2>Expression</h2>
<div id="calculator_visual">Filter</div>
<h2>Result</h2>
<div id="calculator_result">Result</div>