<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<div>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
</div>

<?php if($header === false) { ?>
	<p><?php echo _(sprintf('Unknown data type %s',$type)); ?></p>
<?php } else { ?>
<table id="config_table" class="padd-table">
	<thead>
		<tr>
			<?php $i = 0; foreach ($header as $item) {
				if ($i == 0)
					echo '<th>'.$item.'</th>'."\n";
				else
					echo '<th>'.$item.'</th>'."\n";
				$i++;
			} ?>
		</tr>
	</thead>
	<tbody>
	<?php
		$i = 0;
		if ($data!==false) {
			foreach ($data as $row) {
				$i++;
				echo '<tr class="'.($i%2 == 0 ? 'odd' : 'even').'">'."\n";
				foreach($row as $column) {
					echo '<td style="white-space: normal">'.$column.'</td>'."\n";
				}
				echo '</tr>'."\n";
			}
		} else { ?>
	<tr class="even">
		<td colspan="<?php echo count($header);?>"><?php echo _('No').' '.str_replace('_',' ',$type).' '._('configured'); ?></td>
	</tr>
	<?php } ?>
	</tbody>
</table>
<?php } ?>