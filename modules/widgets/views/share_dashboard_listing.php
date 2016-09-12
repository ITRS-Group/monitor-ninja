<?php

echo "<h2>Who has access to this dashboard</h2>\n";

$display = " style='display: none'";
if(!$shared_to['user'] && !$shared_to['group']) {
	$display = "";
}
echo "<p class='shared_with_placeholder'$display>Looks like you haven't shared this dashboard yet</p>\n";

echo "<ul class='shared_with_these_entities'>\n";

$unshare_link = LinkProvider::factory()
	->get_url('tac', 'unshare_dashboard');
foreach($shared_to as $type => $entities) {
	foreach($entities as $entity) {
		echo "<li class='$type'>
			<span>".html::specialchars($entity)." ($type)</span>
			<a
				class='unshare_dashboard no_uline'
				href='$unshare_link'
				title='Remove access for ".html::specialchars($entity)."'
				data-dashboard-id='".html::specialchars($dashboard_id)."'
				data-group_or_user='$type'
				data-name='".html::specialchars($entity)."'
			><span class='icon-16 x16-delete'></span></a>
		</li>\n";
	}
}

echo "</ul>\n";

