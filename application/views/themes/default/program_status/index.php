<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget w98 left">
	<table>
		<caption><?php echo _('Program status'); ?></caption>
		<thead>
		<tr>
			<th class="headerNone"><?php echo _('Name'); ?></th>
			<th class="headerNone"><?php echo _('Last alive'); ?></th>
			<th class="headerNone"><?php echo _('Is running'); ?></th>
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
			<td colspan="<?php echo count($header);?>"><?php echo _('No program status found'); ?></td>
		</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
