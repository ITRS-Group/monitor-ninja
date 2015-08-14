<?php
print "<li>";
print "<strong>" . $result['name'] . "</strong>";
if ($result['result']) {
	print ' - ' . $result['output'];
} else {
	print '<div class="alert error">'.$result['output'].'</div>';
}
print "</li>";

$this->header = "<ul>";
$this->footer = "</ul>";
$this->footer .= '<input style="margin-left: 12px" type="button" value="Back" onclick="history.go(-2)" />';
$this->footer .= '<input style="margin-left: 12px" type="button" value="Show changes in Nacoma" onclick="window.location=\'' . config::get('core.site_domain') . 'index.php/configuration/configure/?page=export.php\';" />';
