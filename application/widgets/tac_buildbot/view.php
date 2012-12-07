<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table class="w-table">

<?php if (empty($builders)) {?>
<tr>
<td>
<?php echo "Found no buildbot builders at $url :(" ?>
</td>
</tr>

<?php
}
else
{?>
	<colgroup>
		<col style="width: 7%" />
		<col style="width: 31%" />
		<col style="width: 31%" />
		<col style="width: 31%" />
	</colgroup>
	<tr>
		<th></th>
		<th>Builder</th>
		<th>Blame</th>
		<th>Text</th>
	</tr>
<?php foreach ($builders as $name => $attributes) {?>

	<?php
			if (implode($attributes->latest_build->text, ' ') === 'build successful') {?>
				<td class="icon dark"><span class="icon-16 x16-shield-ok"></span></td>
		<?php } else { ?>
				<td class="icon dark"><span class="icon-16 x16-shield-critical"></span></td>
		<?php } ?>
				<td><strong><?php echo $name ?></strong></td>
				<td> <?php echo $attributes->latest_build->blame[0] ?>
				<td> <?php echo implode($attributes->latest_build->text, ' ');?> </td>
	</tr>
<?php }
}
?>

</table>
