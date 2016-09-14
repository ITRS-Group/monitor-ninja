<?php

echo "<h2>Who has access to this dashboard</h2>\n";

$display = "";
if($shared_with) {
	$display = " style='display: none'";
}
echo "<p class='shared_with_placeholder'$display>Looks like you haven't shared this dashboard yet</p>\n";

echo "<ul class='shared_with_these_entities'>\n";

$unshare_link = LinkProvider::factory()
	->get_url('tac', 'unshare_dashboard');
foreach($shared_with as $table => $keys) {
	$friendly_table = $table == 'users' ? 'user' : 'group';
	foreach($keys as $key) {
		echo "<li class='$table'>
			<span>".html::specialchars($key)." ($friendly_table)</span>
			<a
				class='unshare_dashboard no_uline'
				href='$unshare_link'
				title='Remove access for ".html::specialchars($key)."'
				data-dashboard-id='".html::specialchars($dashboard_id)."'
				data-table='$table'
				data-key='".html::specialchars($key)."'
			><span class='icon-cancel error'></span></a>
		</li>\n";
	}
}

echo "</ul>\n";

