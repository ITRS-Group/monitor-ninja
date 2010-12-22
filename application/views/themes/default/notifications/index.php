<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php $t = $this->translate; ?>
<div class="widget w98 left">
	<div id="content-header"<?php if (isset($noheader) && $noheader) { ?> style="display:none"<?php } ?>>
	<?php echo form::open('notifications/'.Router::$method.(isset($host_name) ? '/'.$host_name : '').(isset($service) ? '?service='.$service : ''), array('method' => 'get', 'id' => 'notification_form')); ?>
		<?php echo form::dropdown(array('name' => 'type'), $select_strings, $selected_val); ?>
		<input type="checkbox" name="sort_order" value="asc" /> <?php echo $t->_('Older Entries First');?> &nbsp;
		<input type="submit" value="<?php echo $t->_('Update');?>" />
		<input type="hidden" name="service" value="<?php echo $service;?>" />
		<br /><br />
	</form>
<?php echo (isset($pagination)) ? $pagination : ''; ?>
</div><br />
	<table id="host_table" style="margin-top: 0px;">
		<caption style="margin-top: 15px;">
			<span style="float: left; display: block; margin-top: 2px;"><?php echo (isset($label_title)) ? $label_title : $t->_('Notifications'); ?></span>
		</caption>
		<tr>
			<th class="headerNone"><?php echo $t->_('&nbsp;'); ?></th>
			<?php
				$order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
				$field = isset($_GET['sort_field']) ? $_GET['sort_field'] : 'h.host_name';
				$n = 0;
				foreach($header_links as $row) {
					$n++;
					if (isset($row['url_desc'])) {
						echo '<th class="header'.(($order == 'DESC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortUp' : (($order == 'ASC' && strpos($row['url_desc'], $field) == true && isset($row['url_desc'])) ? 'SortDown' : (isset($row['url_desc']) ? '' : 'None'))) .
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
			<td><?php  echo (!empty($row->service_description)) ? html::anchor('extinfo/details/service/'.$row->host_name.'/?service='.$row->service_description, $row->service_description) : $na_str; ?></td>
			<td><?php echo date('Y-m-d H:i:s',$row->start_time); ?></td>
			<td><?php echo html::anchor('config?type=contacts#config'.$row->contact_name, !empty($row->contact_name) ? $row->contact_name: ''); ?></td>
			<td><?php echo html::anchor('config?type=commands#'.$row->command_name, $row->command_name); ?>&nbsp;</td>
			<td><?php echo $row->output; ?></td>
		</tr>
		<?php } } else { ?>
		<tr class="even">
			<td colspan="7"><?php echo $t->_('No notifications'); ?></td>
		</tr>
		<?php } ?>
	</table>
</div>
