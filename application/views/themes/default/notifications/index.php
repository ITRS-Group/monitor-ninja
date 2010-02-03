<div class="widget w98 left">
	<?php //echo (isset($pagination)) ? $pagination : ''; ?>
	<form method="get" action="">
		<strong><?php echo $this->translate->_('Detail level for all contacts');?></strong><br />
		<select onchange="submit()" name="notification_option" style="margin-top: 3px; margin-left: 0px">
			<?php //if($query_type != nagstat::FIND_SERVICE){ ?>
				<option value="<?php echo nagstat::NOTIFICATION_SERVICE_ALL;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_SERVICE_ALL ? ' selected' : ''?>><?php echo $this->translate->_('All service notifications');?></option>
				<option value="<?php echo nagstat::NOTIFICATION_HOST_ALL;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_HOST_ALL ? ' selected' : ''?>><?php echo $this->translate->_('All host notifications');?></option>
			<?php //} ?>
			<option value="<?php echo nagstat::NOTIFICATION_SERVICE_ACK;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_SERVICE_ACK ? ' selected' : ''?>><?php echo $this->translate->_('Service acknowledgements');?></option>
			<option value="<?php echo nagstat::NOTIFICATION_SERVICE_WARNING;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_SERVICE_WARNING ? ' selected' : ''?>><?php echo $this->translate->_('Service warning');?></option>
			<option value="<?php echo nagstat::NOTIFICATION_SERVICE_UNKNOWN;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_SERVICE_UNKNOWN ? ' selected' : ''?>><?php echo $this->translate->_('Service unknown');?></option>
			<option value="<?php echo nagstat::NOTIFICATION_SERVICE_CRITICAL;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_SERVICE_CRITICAL ? ' selected' : ''?>><?php echo $this->translate->_('Service critical');?></option>
			<option value="<?php echo nagstat::NOTIFICATION_SERVICE_RECOVERY;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_SERVICE_RECOVERY ? ' selected' : ''?>><?php echo $this->translate->_('Service recovery');?></option>
			<option value="<?php echo nagstat::NOTIFICATION_SERVICE_FLAP;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_SERVICE_FLAP ? ' selected' : ''?>><?php echo $this->translate->_('Service flapping');?></option>-->
			<?php //if($query_type != nagstat::FIND_SERVICE){ ?>
				<option value="<?php echo nagstat::NOTIFICATION_HOST_ACK;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_HOST_ACK ? ' selected' : ''?>><?php echo $this->translate->_('Host acknowledgements');?></option>
				<option value="<?php echo nagstat::NOTIFICATION_HOST_DOWN;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_HOST_DOWN ? ' selected' : ''?>><?php echo $this->translate->_('Host down');?></option>
				<option value="<?php echo nagstat::NOTIFICATION_HOST_UNREACHABLE;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_HOST_UNREACHABLE ? ' selected' : ''?>><?php echo $this->translate->_('Host unreachable');?></option>
				<option value="<?php echo nagstat::NOTIFICATION_HOST_RECOVERY;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_HOST_RECOVERY ? ' selected' : ''?>><?php echo $this->translate->_('Host recovery');?></option>
				<option value="<?php echo nagstat::NOTIFICATION_HOST_FLAP;?>"<?php echo  $notification_option == nagstat::NOTIFICATION_HOST_FLAP ? ' selected' : ''?>><?php echo $this->translate->_('Host flapping');?></option>
			<?php //} ?>
		</select> &nbsp;
		<input type="checkbox" name="sort_order" value="desc" /> <?php echo $this->translate->_('Older Entries First');?> &nbsp;
		<!--<input type="submit" value="<?php echo $this->translate->_('Update');?>" />-->
		<br /><br />
	</form>

	<table id="host_table">
		<caption>
			<?php echo html::image($this->add_path('icons/16x16/arrow-left.png'), array('alt' => 'Latest archive', 'title' => 'Latest archive', 'style' => 'float: left; margin: 0px 5px 0px -3px; border: 1px solid #cdcdcd ')); ?>
			<span style="float: left; display: block; margin-top: 2px;"><?php echo (isset($label_title)) ? $label_title : $this->translate->_('Log file navigation').': Jan 1 00:00:00 CET 2010 to Present'; ?></span>
			<?php echo html::image($this->add_path('icons/16x16/arrow-right.png'), array('alt' => 'Latest archive', 'title' => 'Latest archive', 'style' => 'float: right; margin: 0px 0px 0px 3px; border: 1px solid #cdcdcd ')); ?>
		</caption>
		<tr>
			<th class="headerNone">&nbsp;</th>
			<th class="headerNone"><?php echo $this->translate->_('Host'); ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Service'); ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Time'); ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Contact'); ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Command'); ?></th>
			<th class="headerNone"><?php echo $this->translate->_('Information'); ?></th>
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
				// services
				if($row->notification_type == nagstat::NOTIFICATION_SERVICE_CRITICAL)
					echo html::image($this->add_path('icons/16x16/shield-critical.png'), array('alt' => 'Critical', 'title' => 'Critical'));
				elseif($row->notification_type == nagstat::NOTIFICATION_SERVICE_RECOVERY)
					echo html::image($this->add_path('icons/16x16/shield-ok.png'), array('alt' => 'Critical', 'title' => 'Critical'));
				elseif($row->notification_type == nagstat::NOTIFICATION_SERVICE_ACK)
					echo html::image($this->add_path('icons/16x16/acknowledged.png'), array('alt' => 'Critical', 'title' => 'Critical'));
				elseif($row->notification_type == nagstat::NOTIFICATION_SERVICE_FLAP)
					echo html::image($this->add_path('icons/16x16/flapping.png'), array('alt' => 'Critical', 'title' => 'Critical'));
				elseif($row->notification_type == nagstat::NOTIFICATION_SERVICE_UNKNOWN)
					echo html::image($this->add_path('icons/16x16/shield-uknown.png'), array('alt' => 'Critical', 'title' => 'Critical'));
				// hosts
				elseif($row->notification_type == nagstat::NOTIFICATION_HOST_DOWN)
					echo html::image($this->add_path('icons/16x16/shield-down.png'), array('alt' => 'Critical', 'title' => 'Critical'));
				elseif($row->notification_type == nagstat::NOTIFICATION_HOST_UNREACHABLE)
					echo html::image($this->add_path('icons/16x16/shield-unreachable.png'), array('alt' => 'Critical', 'title' => 'Critical'));
				elseif($row->notification_type == nagstat::NOTIFICATION_HOST_RECOVERY)
					echo html::image($this->add_path('icons/16x16/shield-ok.png'), array('alt' => 'Critical', 'title' => 'Critical'));
				elseif($row->notification_type == nagstat::NOTIFICATION_HOST_ACK)
					echo html::image($this->add_path('icons/16x16/shield-critical.png'), array('alt' => 'Critical', 'title' => 'Critical'));
				elseif($row->notification_type == nagstat::NOTIFICATION_HOST_FLAP)
					echo html::image($this->add_path('icons/16x16/shield-critical.png'), array('alt' => 'Critical', 'title' => 'Critical'));
				?>
			</td>
			<td><?php echo $row->host_name; ?></td>
			<td><?php echo $row->service_description; ?></td>
			<td><?php echo date('Y-m-d H:i:s',$row->start_time); ?></td>
			<td><?php echo $row->contact_name; ?></td>
			<td><?php echo $row->state; //reason_type ?></td>
			<td><?php echo $row->output; ?></td>
		</tr>
		<?php } } else { ?>
		<tr class="even">
			<td colspan="7"><?php echo $this->translate->_('No notifications'); ?></td>
		</tr>
		<?php } ?>
	</table>
</div>