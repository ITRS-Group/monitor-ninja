<?php defined('SYSPATH') OR die('No direct access allowed.');
$notes_url_target = config::get('nagdefault.notes_url_target', '*');
$action_url_target = config::get('nagdefault.action_url_target', '*');
?>
<div id="page_links">
	<ul>
	<?php
	if (isset($page_links)) {
		foreach ($page_links as $label => $link) {
			?>
			<li><?php echo html::anchor($link, $label) ?></li>
			<?php
		}
	}
	?>
	</ul>
</div>
<div class="clear"> </div>

<?php if (!empty($action_url)) { ?>
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
<h1><?php echo ucfirst($label_grouptype); ?> <strong><?php echo $object->get_alias(); ?> (<?php echo $object->get_key() ?>)</h1>
<?php
/*
		<?php if (nacoma::link()===true)
		TODO add configuration commands to all objects
			echo nacoma::link('configuration/configure/'.$grouptype.'/'.urlencode($groupname), 'icons/x16/nacoma.png', sprintf(_('Configure this %s'), $grouptype));?>
	</caption>
 */

echo $commands;
