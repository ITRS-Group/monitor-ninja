<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php $t = $this->translate; ?>
<div class="widget w98 left">
	<form method="get" action="">
		<strong><?php echo $t->_('Detail level for all contacts');?></strong><br />
		<select onchange="submit()" name="notification_option" style="margin-top: 3px; margin-left: 0px">
			<?php if($query_type == nagstat::FIND_SERVICE){ ?>
				<option value="<?php echo nagstat::NOTIFICATION_SERVICE_ALL;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_SERVICE_ALL ? ' selected' : ''?>><?php echo $t->_('All service notifications');?></option>
				<option value="<?php echo nagstat::NOTIFICATION_HOST_ALL;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_HOST_ALL ? ' selected' : ''?>><?php echo $t->_('All host notifications');?></option>
			<?php } ?>
			<option value="<?php echo nagstat::NOTIFICATION_SERVICE_ACK;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_SERVICE_ACK ? ' selected' : ''?>><?php echo $t->_('Service acknowledgements');?></option>
			<option value="<?php echo nagstat::NOTIFICATION_SERVICE_WARNING;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_SERVICE_WARNING ? ' selected' : ''?>><?php echo $t->_('Service warning');?></option>
			<option value="<?php echo nagstat::NOTIFICATION_SERVICE_UNKNOWN;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_SERVICE_UNKNOWN ? ' selected' : ''?>><?php echo $t->_('Service unknown');?></option>
			<option value="<?php echo nagstat::NOTIFICATION_SERVICE_CRITICAL;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_SERVICE_CRITICAL ? ' selected' : ''?>><?php echo $t->_('Service critical');?></option>
			<option value="<?php echo nagstat::NOTIFICATION_SERVICE_RECOVERY;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_SERVICE_RECOVERY ? ' selected' : ''?>><?php echo $t->_('Service recovery');?></option>
			<option value="<?php echo nagstat::NOTIFICATION_SERVICE_FLAP;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_SERVICE_FLAP ? ' selected' : ''?>><?php echo $t->_('Service flapping');?></option>-->
			<?php if($query_type == nagstat::FIND_HOST){ ?>
				<option value="<?php echo nagstat::NOTIFICATION_HOST_ACK;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_HOST_ACK ? ' selected' : ''?>><?php echo $t->_('Host acknowledgements');?></option>
				<option value="<?php echo nagstat::NOTIFICATION_HOST_DOWN;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_HOST_DOWN ? ' selected' : ''?>><?php echo $t->_('Host down');?></option>
				<option value="<?php echo nagstat::NOTIFICATION_HOST_UNREACHABLE;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_HOST_UNREACHABLE ? ' selected' : ''?>><?php echo $t->_('Host unreachable');?></option>
				<option value="<?php echo nagstat::NOTIFICATION_HOST_RECOVERY;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_HOST_RECOVERY ? ' selected' : ''?>><?php echo $t->_('Host recovery');?></option>
				<option value="<?php echo nagstat::NOTIFICATION_HOST_FLAP;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_HOST_FLAP ? ' selected' : ''?>><?php echo $t->_('Host flapping');?></option>
			<?php } ?>
		</select> &nbsp;
		<input type="checkbox" name="sort_order" value="desc" /> <?php echo $t->_('Older Entries First');?> &nbsp;
		<!--<input type="submit" value="<?php echo $t->_('Update');?>" />-->
		<br /><br />
	</form>
<?php echo (isset($pagination)) ? $pagination : ''; ?>
	<table id="host_table" style="margin-top: 0px;">
		<caption style="margin-top: -15px;">
			<span style="float: left; display: block; margin-top: 2px;"><?php echo (isset($label_title)) ? $label_title : $t->_('Notifications'); ?></span>
		</caption>
		<tr>
			<th class="headerNone">&nbsp;</th>
			<th class="headerNone"><?php echo $t->_('Host'); ?></th>
			<th class="headerNone"><?php echo $t->_('Service'); ?></th>
			<th class="headerNone"><?php echo $t->_('Time'); ?></th>
			<th class="headerNone"><?php echo $t->_('Contact'); ?></th>
			<th class="headerNone"><?php echo $t->_('Notification Command'); ?></th>
			<th class="headerNone"><?php echo $t->_('Information'); ?></th>
		</tr>
		<?php
			$i = 0;
			if ($data!==false && $data->count()) {
				foreach ($data as $row) {
					$i++;
		?>
		<tr class="<?php echo $i%2 == 0 ? 'odd' : 'even'; ?>">
			<td class="icon status">
				<?php
				//echo $row->state;
				/*if($row->notification_type == nagstat::SERVICE_NOTIFICATION) {
					if($row->state == nagstat::SERVICE_CRITICAL)
						echo html::image($this->add_path('icons/16x16/shield-critical.png'), array('alt' => $t->_('Critical'), 'title' => $t->_('Critical')));
					elseif($row->state == nagstat::SERVICE_RECOVERY)
						echo html::image($this->add_path('icons/16x16/shield-ok.png'), array('alt' => $t->_('Recovery'), 'title' => $t->_('Recovery')));
					elseif($row->state == nagstat::SERVICE_WARNING)
						echo html::image($this->add_path('icons/16x16/shield-warning.png'), array('alt' => $t->_('Warning'), 'title' => $t->_('Warning')));
					elseif($row->state == nagstat::NOTIFICATION_SERVICE_ACK)
						echo html::image($this->add_path('icons/16x16/acknowledged.png'), array('alt' => $t->_('Acknowledged'), 'title' => $t->_('Acknowledged')));
					elseif($row->state == nagstat::NOTIFICATION_SERVICE_FLAP)
						echo html::image($this->add_path('icons/16x16/flapping.png'), array('alt' => $t->_('Flapping'), 'title' => $t->_('Flapping')));
					elseif($row->state == nagstat::NOTIFICATION_SERVICE_UNKNOWN)
						echo html::image($this->add_path('icons/16x16/shield-uknown.png'), array('alt' => $t->_('Unknown'), 'title' => $t->_('Unknown')));
				}
				elseif($row->notification_type == nagstat::HOST_NOTIFICATION) {
					if($row->state == nagstat::HOST_DOWN)
						echo html::image($this->add_path('icons/16x16/shield-down.png'), array('alt' => $t->_('Down'), 'title' => $t->_('Down')));
					elseif($row->state == nagstat::HOST_UNREACHABLE)
						echo html::image($this->add_path('icons/16x16/shield-unreachable.png'), array('alt' => $t->_('Unreachable'), 'title' => $t->_('Unreachable')));
					elseif($row->state == nagstat::NOTIFICATION_HOST_RECOVERY)
						echo html::image($this->add_path('icons/16x16/shield-ok.png'), array('alt' => $t->_('Recovery'), 'title' => $t->_('Recovery')));
					elseif($row->state == nagstat::NOTIFICATION_HOST_ACK)
						echo html::image($this->add_path('icons/16x16/shield-critical.png'), array('alt' => $t->_('Acknowledged'), 'title' => $t->_('Acknowledged')));
					elseif($row->state == nagstat::NOTIFICATION_HOST_FLAP)
						echo html::image($this->add_path('icons/16x16/shield-critical.png'), array('alt' => $t->_('Flapping'), 'title' => $t->_('Flapping')));
				}*/
				if($row->state == 1)
						echo html::image($this->add_path('icons/16x16/shield-warning.png'), array('alt' => $t->_('Warning'), 'title' => $t->_('Warning')));
				elseif($row->state == 2)
						echo html::image($this->add_path('icons/16x16/shield-critical.png'), array('alt' => $t->_('Critical'), 'title' => $t->_('Critical')));
				elseif($row->state == 3)
					echo html::image($this->add_path('icons/16x16/shield-unknown.png'), array('alt' => $t->_('Unknown'), 'title' => $t->_('Unknown')));
				elseif($row->state == 4)
					echo html::image($this->add_path('icons/16x16/shield-ok.png'), array('alt' => $t->_('Recovery'), 'title' => $t->_('Recovery')));
				elseif($row->state == 8) // ?
					echo html::image($this->add_path('icons/16x16/acknowledged.png'), array('alt' => $t->_('Acknowledged'), 'title' => $t->_('Acknowledged')));
				elseif($row->state == 16) //?
					echo html::image($this->add_path('icons/16x16/flapping.png'), array('alt' => $t->_('Flapping'), 'title' => $t->_('Flapping')));
				?>
			</td>
			<td><?php echo html::anchor('extinfo/details/host/'.$row->host_name, $row->host_name); ?></td>
			<td><?php echo html::anchor('extinfo/details/service/'.$row->host_name.'/?service='.$row->service_description, $row->service_description); ?></td>
			<td><?php echo date('Y-m-d H:i:s',$row->start_time); ?></td>
			<td><?php echo html::anchor('config/contacts/'.$row->contact_name, !empty($row->contact_name) ? $row->contact_name: ''); ?></td>
			<td><?php echo html::anchor('config/commands/'.$row->reason_type, $row->reason_type); ?></td>
			<td><?php echo $row->output; ?></td>
		</tr>
		<?php } } else { ?>
		<tr class="even">
			<td colspan="7"><?php echo $t->_('No notifications'); ?></td>
		</tr>
		<?php } ?>
	</table>
</div>