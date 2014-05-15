<?php
if ($result === true) {
	echo '<div class="alert notice">'.sprintf(_('Your command was successfully submitted to %s.'), Kohana::config('config.product_name')).'</div>'.
			 '<input style="margin-left: 12px" type="button" value="Done" onclick="history.go(-2)" />'."\n";
} else {
	echo '<div class="alert error">'.sprintf(_('There was an error submitting your command to %s.'), Kohana::config('config.product_name'));
	if (!empty($error)) {
		echo '<br /><br />'._('ERROR').': '.$error;
	}
	echo "</div>\n";
}
