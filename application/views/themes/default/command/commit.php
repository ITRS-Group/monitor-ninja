<?php
echo "<br />";
if ($result === true) {
	echo '<div>'.sprintf(_('Your command was successfully submitted to %s.'), Kohana::config('config.product_name')).'<br /><br />'.
			 '<input type="button" value="Done" onclick="history.go(-2)" /></div>'."\n";
} else {
	echo '<div>'.sprintf(_('There was an error submitting your command to %s.'), Kohana::config('config.product_name'));
	if (!empty($error)) {
		echo '<br /><br />'._('ERROR').': '.$error;
	}
	echo "</div>\n";
}
