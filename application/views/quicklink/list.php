<h2>Currently saved quicklinks</h2>
<?php

$display = "";
if($quicklinks) {
	$display = " style='display: none'";
}
echo "<p class='quicklinks_placeholder'$display>Looks like you haven't created any quicklinks yet</p>\n";

echo "<ul>\n";

$delete_link = LinkProvider::factory()
	->get_url('quicklink', 'delete_quicklink');
foreach($quicklinks as $quicklink) {
	// Quicklinks have no IDs. Let's treat the title/href pair as a
	// composite key when referring to a quicklink that is about to
	// be deleted.
	echo "<li>
		<span>
			<span class='icon-16 x16-".html::specialchars($quicklink["icon"])."'></span>
			<a href='".html::specialchars($quicklink["href"])."' target='_blank'>".
			html::specialchars($quicklink["title"]).
			"</a>".
			" (".html::specialchars($quicklink["href"]).")
		</span>
		<a
			class='remove_quicklink no_uline'
			href='$delete_link'
			title='Remove this quicklink'
			data-title='".html::specialchars($quicklink["title"])."'
			data-href='".html::specialchars($quicklink["href"])."'
		><span class='icon-cancel error'></span></a>
	</li>\n";
}

echo "</ul>\n";
