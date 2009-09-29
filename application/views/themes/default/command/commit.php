<?php
echo "<br />";
if ($result === true) {
	echo '<div class="widget w32 left">'.$this->translate->_('Your command was successfully submitted to Nagios.').'<br /><br />'.
			 '<input type="button" value="Done" onclick="location.href=\''.url::site('/extinfo/details/'.$this->session->get('back_extinfo')).'\'" /></div>'."\n";
} else {
	echo '<div class="widget w32 left">'.$this->translate->_('There was an error submitting your command to Nagios.');
	if (!empty($error)) {
		echo '<br /><br />'.$this->translate->_('ERROR').': '.$error;
	}
	echo "</div>\n";
}
