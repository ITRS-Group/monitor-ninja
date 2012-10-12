<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="widget w98 left config_header" style="top: 57px; position: fixed; background-color: #ffffff; padding: 10px 1%">
	<form method="get" action="">
	<?php echo _('Object type'); ?>:
	<select class="auto" name="type" onchange="submit()">
		<option value="hosts"<?php echo $type == 'hosts' ? ' selected="selected"' : '';?>><?php echo _('Hosts');?>
		<option value="hostgroups"<?php echo $type == 'hostgroups' ? ' selected="selected"' : '';?>><?php echo _('Host Groups');?>
		<option value="services"<?php echo $type == 'services' ? ' selected="selected"' : '';?>><?php echo _('Services');?>
		<option value="servicegroups"<?php echo $type == 'servicegroups' ? ' selected="selected"' : '';?>><?php echo _('Service Groups');?>
		<option value="contacts"<?php echo $type == 'contacts' ? ' selected="selected"' : '';?>><?php echo _('Contacts');?>
		<option value="contactgroups"<?php echo $type == 'contactgroups' ? ' selected="selected"' : '';?>><?php echo _('Contact Groups');?>
		<option value="timeperiods"<?php echo $type == 'timeperiods' ? ' selected="selected"' : '';?>><?php echo _('Timeperiods');?>
		<option value="commands"<?php echo $type == 'commands' ? ' selected="selected"' : '';?>><?php echo _('Commands');?>
	</select>
	<input type="text" id="filterbox" name="filterbox" value="<?php echo $filter_string ?>" />
	<input type="submit" value="<?php echo _("Filter") ?>"  />
	</form>
</div>
<div class="widget w98 left" style="margin-top: 40px">
	<table id="config_table">
		<thead>
		<tr>
			<?php $i = 0; foreach ($header as $item) {
				if ($i == 0)
					echo '<th class="headerSortDown">'.$item.'</th>'."\n";
				else
					echo '<th class="header">'.$item.'</th>'."\n";
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
</div>
