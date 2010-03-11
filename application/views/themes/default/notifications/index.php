<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php $t = $this->translate; ?>
<div class="widget w98 left">
	<div id="content-header"<?php if (isset($noheader) && $noheader) { ?> style="display:none"<?php } ?>>
	<?php echo form::open('notifications/'.Router::$method.(isset($host_name) ? '/'.$host_name : ''), array('method' => 'get', 'id' => 'notification_form')); ?>
		<strong><?php echo $t->_('Detail level for all contacts');?></strong><br />
		<?php echo form::dropdown(array('name' => 'type'), $select_strings, $selected_val); ?>
		<input type="checkbox" name="sort_order" value="asc" /> <?php echo $t->_('Older Entries First');?> &nbsp;
		<input type="submit" value="<?php echo $t->_('Update');?>" />
		<br /><br />
	</form>
<?php echo (isset($pagination)) ? $pagination : ''; ?>
</div><br />
	<table id="host_table" style="margin-top: 0px;">
		<caption style="margin-top: -15px;">
			<span style="float: left; display: block; margin-top: 2px;"><?php echo (isset($label_title)) ? $label_title : $t->_('Notifications'); ?></span>
		</caption>
		<tr>
			<th class="headerNone"><?php echo $t->_('&nbsp;'); ?></th>
			<th class="headerNone"><?php echo $t->_('Host'); ?></th>
			<th class="headerNone"><?php echo $t->_('Service'); ?></th>
			<th class="headerNone"><?php echo $t->_('Time'); ?></th>
			<th class="headerNone"><?php echo $t->_('Contact'); ?></th>
			<th class="headerNone"><?php echo $t->_('Notification Command'); ?></th>
			<th class="headerNone"><?php echo $t->_('Information'); ?></th>
		</tr>
		<?php
			$i = 0;
			if ($data!==false && count($data)) {
				foreach ($data as $row) {
					$i++;
		?>
		<tr class="<?php echo $i%2 == 0 ? 'odd' : 'even'; ?>">
			<td class="icon status" style="text-align: left">
				<?php
				//echo $row->reason_type.nagstat::NOTIFICATION_SERVICE_ACK;
				if($row->notification_type == nagstat::SERVICE_NOTIFICATION) {
					// state
					if($row->state == nagstat::NOTIFICATION_SERVICE_RECOVERY)
						echo html::image($this->add_path('icons/16x16/shield-ok.png'), array('alt' => $t->_('Recovery'), 'title' => $t->_('Recovery')));
					elseif($row->state == nagstat::NOTIFICATION_SERVICE_CRITICAL)
						echo html::image($this->add_path('icons/16x16/shield-critical.png'), array('alt' => $t->_('Critical'), 'title' => $t->_('Critical')));
					elseif($row->state == nagstat::NOTIFICATION_SERVICE_WARNING)
						echo html::image($this->add_path('icons/16x16/shield-warning.png'), array('alt' => $t->_('Warning'), 'title' => $t->_('Warning')));
					elseif($row->state == nagstat::NOTIFICATION_SERVICE_UNKNOWN)
						echo html::image($this->add_path('icons/16x16/shield-unknown.png'), array('alt' => $t->_('Unknown'), 'title' => $t->_('Unknown')));
					// reason type
					if($row->reason_type == nagstat::NOTIFICATION_SERVICE_ACK)
						echo html::image($this->add_path('icons/16x16/acknowledged.png'), array('alt' => $t->_('Acknowledged'), 'title' => $t->_('Acknowledged')));
					elseif($row->reason_type == nagstat::NOTIFICATION_SERVICE_FLAP)
						echo html::image($this->add_path('icons/16x16/flapping.gif'), array('alt' => $t->_('Flapping'), 'title' => $t->_('Flapping')));
				}
				elseif($row->notification_type == nagstat::HOST_NOTIFICATION) {
					// state
					if($row->state == nagstat::NOTIFICATION_HOST_DOWN)
						echo html::image($this->add_path('icons/16x16/shield-down.png'), array('alt' => $t->_('Down'), 'title' => $t->_('Down')));
					elseif($row->state == nagstat::NOTIFICATION_HOST_UNREACHABLE)
						echo html::image($this->add_path('icons/16x16/shield-unreachable.png'), array('alt' => $t->_('Unreachable'), 'title' => $t->_('Unreachable')));
					elseif($row->state == nagstat::NOTIFICATION_HOST_RECOVERY)
						echo html::image($this->add_path('icons/16x16/shield-ok.png'), array('alt' => $t->_('Recovery'), 'title' => $t->_('Recovery')));
					// reason type
					if($row->reason_type == nagstat::NOTIFICATION_HOST_ACK)
						echo html::image($this->add_path('icons/16x16/acknowledged.png'), array('alt' => $t->_('Acknowledged'), 'title' => $t->_('Acknowledged')));
					elseif($row->reason_type == nagstat::NOTIFICATION_HOST_FLAP)
						echo html::image($this->add_path('icons/16x16/flapping.gif'), array('alt' => $t->_('Flapping'), 'title' => $t->_('Flapping')));
				}
				?>
			</td>
			<td><?php echo html::anchor('extinfo/details/host/'.$row->host_name, $row->host_name); ?></td>
			<td><?php echo html::anchor('extinfo/details/service/'.$row->host_name.'/?service='.$row->service_description, $row->service_description); ?></td>
			<td><?php echo date('Y-m-d H:i:s',$row->start_time); ?></td>
			<td><?php echo html::anchor('config?type=contacts#config'.$row->contact_name, !empty($row->contact_name) ? $row->contact_name: ''); ?></td>
			<td><?php //echo html::anchor('config?type=commands#'.$row->notification_command, $row->notification_command); ?>&nbsp;</td>
			<td><?php echo $row->output; ?></td>
		</tr>
		<?php } } else { ?>
		<tr class="even">
			<td colspan="7"><?php echo $t->_('No notifications'); ?></td>
		</tr>
		<?php } ?>
	</table>
</div>