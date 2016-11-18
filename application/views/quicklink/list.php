<?php
if(!$quicklinks) {
	return;
}
?>
<h2>Saved quicklinks</h2>
<?php

echo "<table>\n
<tr>
	<th>Icon</th>
	<th>Title</th>
	<th>Link</th>
	<th>Action</th>
</tr>

";

$delete_link = LinkProvider::factory()
	->get_url('quicklink', 'delete_quicklink');
foreach($quicklinks as $quicklink) {
	// Quicklinks have no IDs. Let's treat the title/href pair as a
	// composite key when referring to a quicklink that is about to
	// be deleted.
	$max_length = 30;

	$printable_title = $quicklink["title"];
	if(strlen($printable_title) >= $max_length) {
		$printable_title = substr($printable_title, 0, $max_length). "...";
	}

	$printable_href = $quicklink["href"];
	if(strlen($printable_href) >= $max_length) {
		$printable_href = substr($printable_href, 0, $max_length). "...";
	}
	echo "<tr>
		<td>
			<span class='icon-16 x16-".html::specialchars($quicklink["icon"])."'></span>
		</td>
		<td>
			<a href='".html::specialchars($quicklink["href"])."' title='".html::specialchars($quicklink["title"])."' target='_blank'>".
			html::specialchars($printable_title).
			"</a>
		</td>
		<td>
			".html::specialchars($printable_href)."
		</td>
		<td>
			<a
				class='remove_quicklink no_uline'
				href='$delete_link'
				title='Remove this quicklink'
				data-title='".html::specialchars($quicklink["title"])."'
				data-href='".html::specialchars($quicklink["href"])."'
			><span class='icon-cancel error'></span></a>
		</td>
	</tr>\n";
}

echo "</table>\n";
