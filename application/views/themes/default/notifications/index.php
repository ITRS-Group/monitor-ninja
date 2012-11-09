<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<h1><?php echo (isset($label_title)) ? $label_title : _('Notifications'); ?></h1>
<hr />
<div>
	<div id="content-header"<?php if (isset($noheader) && $noheader) { ?> style="display:none"<?php } ?>>
	<?php echo form::open('notifications/'.Router::$method.(isset($host_name) ? '/'.$host_name : '').(isset($service) ? '?service='.$service : ''), array('method' => 'get', 'id' => 'notification_form')); ?>
		<?php echo form::dropdown(array('name' => 'type', 'class' => 'auto'), $select_strings, $selected_val); ?>
		<input type="submit" value="<?php echo _('Update');?>" />
		<input type="hidden" name="service" value="<?php echo $service;?>" />
	</form>
<?php echo (isset($pagination)) ? $pagination : ''; ?>
</div><br />
	<table id="host_table" style="margin-top: 0px;">
		<tr>
			<th><?php echo _('&nbsp;'); ?></th>
			<?php
				$order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
				$field = isset($_GET['sort_field']) ? $_GET['sort_field'] : 'h.host_name';
				$n = 0;
				foreach($header_links as $row) {
					$n++;
					if (isset($row['url_desc'])) {
						echo '<th class="'.(($order == 'DESC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortUp' : (($order == 'ASC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortDown' : (isset($row['url_desc']) ? '' : 'None'))) .
							'" onclick="location.href=\'' . url::site() .((isset($row['url_desc']) && $order == 'ASC') ? $row['url_desc'] : ((isset($row['url_asc']) && $order == 'DESC') ? $row['url_asc'] : '')).'\'">';
						echo $row['title'];
						echo '</th>';
					}
				}
			?>
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
						echo html::image($this->add_path('icons/16x16/shield-ok.png'), array('alt' => _('Recovery'), 'title' => _('Recovery')));
					elseif($row->state == nagstat::NOTIFICATION_SERVICE_CRITICAL)
						echo html::image($this->add_path('icons/16x16/shield-critical.png'), array('alt' => _('Critical'), 'title' => _('Critical')));
					elseif($row->state == nagstat::NOTIFICATION_SERVICE_WARNING)
						echo html::image($this->add_path('icons/16x16/shield-warning.png'), array('alt' => _('Warning'), 'title' => _('Warning')));
					elseif($row->state == nagstat::NOTIFICATION_SERVICE_UNKNOWN)
						echo html::image($this->add_path('icons/16x16/shield-unknown.png'), array('alt' => _('Unknown'), 'title' => _('Unknown')));
					// reason type
					if($row->reason_type == nagstat::NOTIFICATION_SERVICE_ACK)
						echo html::image($this->add_path('icons/16x16/acknowledged.png'), array('alt' => _('Acknowledged'), 'title' => _('Acknowledged')));
					elseif($row->reason_type == nagstat::NOTIFICATION_SERVICE_FLAP)
						echo html::image($this->add_path('icons/16x16/flapping.gif'), array('alt' => _('Flapping'), 'title' => _('Flapping')));
				}
				elseif($row->notification_type == nagstat::HOST_NOTIFICATION) {
					// state
					if($row->state == nagstat::NOTIFICATION_HOST_DOWN)
						echo html::image($this->add_path('icons/16x16/shield-down.png'), array('alt' => _('Down'), 'title' => _('Down')));
					elseif($row->state == nagstat::NOTIFICATION_HOST_UNREACHABLE)
						echo html::image($this->add_path('icons/16x16/shield-unreachable.png'), array('alt' => _('Unreachable'), 'title' => _('Unreachable')));
					elseif($row->state == nagstat::NOTIFICATION_HOST_RECOVERY)
						echo html::image($this->add_path('icons/16x16/shield-ok.png'), array('alt' => _('Recovery'), 'title' => _('Recovery')));
					// reason type
					if($row->reason_type == nagstat::NOTIFICATION_HOST_ACK)
						echo html::image($this->add_path('icons/16x16/acknowledged.png'), array('alt' => _('Acknowledged'), 'title' => _('Acknowledged')));
					elseif($row->reason_type == nagstat::NOTIFICATION_HOST_FLAP)
						echo html::image($this->add_path('icons/16x16/flapping.gif'), array('alt' => _('Flapping'), 'title' => _('Flapping')));
				}
				?>
			</td>
			<td><?php echo html::anchor('extinfo/details/host/'.$row->host_name, $row->host_name); ?></td>
			<td><?php  echo (!empty($row->service_description)) ? html::anchor('extinfo/details/service/'.$row->host_name.'/?service='.$row->service_description, $row->service_description) : _('N/A'); ?></td>
			<td><?php echo date($date_format_str,$row->start_time); ?></td>
			<td><?php echo html::anchor('config?type=contacts#config'.$row->contact_name, !empty($row->contact_name) ? $row->contact_name: ''); ?></td>
			<td><?php echo (!empty($row->command_name)) ? html::anchor('config?type=commands#'.$row->command_name, $row->command_name) : ''; ?>&nbsp;</td>
			<td><?php echo $row->output; ?></td>
		</tr>
		<?php } } else { ?>
		<tr class="even">
			<td colspan="7"><?php echo _('No notifications'); ?></td>
		</tr>
		<?php } ?>
	</table>
</div>
