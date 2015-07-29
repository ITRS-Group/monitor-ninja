<?php defined('SYSPATH') OR die('No direct access allowed.');

/* @var $set ObjectSet_Model */
$it = $set->it(false, array(), 1, 0);
/* @var $object Object_Model */
$object = $it->current();

/* @var $object Object_Model */
$commands = $object->list_commands();

?>
<div class="right width-33" id="extinfo_info">
	<table class="ext">
<?php foreach($commands as $cmd => $cmdinfo): ?>
		<tr>
			<td class="icon dark">
				<span class="icon-16 x16-<?php echo $cmdinfo['icon']; ?>" title="<?php echo  html::specialchars($cmdinfo['name']) ?>"></span>
			</td>
			<td class="bt"><?php echo nagioscmd::cmd_link($object, $cmd, $cmdinfo['name']) ?></td>
		</tr>
<?php endforeach; ?>
	</table>
</div>

