<?php defined('SYSPATH') OR die('No direct access allowed.');
$notes_url_target = config::get('nagdefault.notes_url_target', '*');
$action_url_target = config::get('nagdefault.action_url_target', '*');


if (!empty($action_url)) { ?>
<a href="<?php echo $action_url ?>" style="border: 0px" target="<?php echo $action_url_target ?>">
	<span class="icon-16 x16-host-actions" title="Perform extra host actions"></span>
<br />
<strong><?php echo _('Extra actions') ?></strong>
<?php } ?>
<br />

<?php if (!empty($notes_url)) { ?>
<a href="<?php echo $notes_url ?>" style="border: 0px" target="<?php echo $notes_url_target ?>">
	<span class="icon-16 x16-host-notes" title="View extra host notes"></span>
<br />
<strong><?php echo _('Extra notes') ?></strong>
<?php }

if (!empty($notes)) {?>
	<br /><strong><?php echo _('Notes') ?></strong>: <?php echo $notes;
}

?>

<div>
<div class="information-component">
<h1><?php
	echo ucfirst($type) . ': ' . $object->get_alias() . '(' . $object->get_key() . ')'; ?></h1>
</div>
<?php

if ($object->get_table() === 'hostgroups') {
	View::factory('extinfo/components/host_states', array(
		'object' => $object
	))->render(true);
}

View::factory('extinfo/components/service_states', array(
	'object' => $object
))->render(true);

/* @var $widgets widget_Base[] */
foreach ($widgets as $title => $widget) {
	echo '<div class="information-component information-component-fullwidth">';
	echo '<div class="information-component-title">' . $title . '</div>';
	echo $widget->render('index', false);
	echo '</div>';
}

