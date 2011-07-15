<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php $t = $this->translate; ?>
<div class="widget w98 left config_header" style="top: 57px; position: fixed; background-color: #ffffff; padding: 10px 1%">
	<div class="ie-pag-config" style="position: absolute; right: 235px;"><?php echo (isset($pagination)) ? $pagination : ''; ?></div>
	<form method="get" action="">
	<?php echo $t->_('Object type'); ?>:
	<select name="type" onchange="submit()">
		<option value="hosts"<?php echo $type == 'hosts' ? ' selected="selected"' : '';?>><?php echo $t->_('Hosts');?>
		<option value="host_dependencies"<?php echo $type == 'host_dependencies' ? ' selected="selected"' : '';?>><?php echo $t->_('Host Dependencies');?>
		<option value="host_escalations"<?php echo $type == 'host_escalations' ? ' selected="selected"' : '';?>><?php echo $t->_('Host Escalations');?>
		<option value="host_groups"<?php echo $type == 'host_groups' ? ' selected="selected"' : '';?>><?php echo $t->_('Host Groups');?>
		<option value="services"<?php echo $type == 'services' ? ' selected="selected"' : '';?>><?php echo $t->_('Services');?>
		<option value="service_groups"<?php echo $type == 'service_groups' ? ' selected="selected"' : '';?>><?php echo $t->_('Service Groups');?>
		<option value="service_dependencies"<?php echo $type == 'service_dependencies' ? ' selected="selected"' : '';?>><?php echo $t->_('Service Dependencies');?>
		<option value="service_escalations"<?php echo $type == 'service_escalations' ? ' selected="selected"' : '';?>><?php echo $t->_('Service Escalations');?>
		<option value="contacts"<?php echo $type == 'contacts' ? ' selected="selected"' : '';?>><?php echo $t->_('Contacts');?>
		<option value="contact_groups"<?php echo $type == 'contact_groups' ? ' selected="selected"' : '';?>><?php echo $t->_('Contact Groups');?>
		<option value="timeperiods"<?php echo $type == 'timeperiods' ? ' selected="selected"' : '';?>><?php echo $t->_('Timeperiods');?>
		<option value="commands"<?php echo $type == 'commands' ? ' selected="selected"' : '';?>><?php echo $t->_('Commands');?>
		<!--<option value="extended_host_information"<?php echo $type == 'extended_host_information' ? ' selected="selected"' : '';?>><?php echo $t->_('Extended Host Information');?>
		<option value="extended_service_information"<?php echo $type == 'extended_service_information' ? ' selected="selected"' : '';?>><?php echo $t->_('Extended Service Information');?>-->
	</select>
	<?php echo form::input(array('id' => 'filterbox', 'style' => 'color:grey'), $filter_string) ?>
	</form>
</div>
<div class="widget w98 left" style="margin-top: 40px">
	<table id="config_table">
		<!--<caption><?php echo ucfirst(str_replace('_',' ',$type)); ?></caption>-->
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
			<td colspan="<?php echo count($header);?>"><?php echo $t->_('No').' '.str_replace('_',' ',$type).' '.$t->_('configured'); ?></td>
		</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
