<?php

$query = "[" . $object->get_table() . "]";

if ($object->get_table() === 'services') {
    $query .= ' host.name="' . $object->get_host()->get_name() . '" and description = "' . $object->get_description() . '"';
} else {
    $query .= ' name="' . $object->get_name() . '"';
}

?>
<div
	data-setting-toggle-command="<?php echo $command; ?>"
	data-setting-toggle-query="<?php echo rawurlencode($query); ?>"
></div>

