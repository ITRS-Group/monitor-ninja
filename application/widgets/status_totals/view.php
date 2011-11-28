<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<table class="w-table w50">
<?php $i=0; foreach ($host_header as $row) { ?>
	<?php echo ($i%2 == 0) ? '<tr>' : '' ?>
		<td class="status icon" style="padding: 4px 7px;">
			<?php
			if ($row['lable'] > 0)
				echo html::image($this->add_path('icons/12x12/shield-'.strtolower($row['status']).'.png'),array('title' => $row['status'], 'alt' => $row['status'], 'style' => 'margin-bottom: -2px'));
			else
				echo html::image($this->add_path('icons/12x12/shield-not-'.strtolower($row['status']).'.png'),array('title' => $row['status'], 'alt' => $row['status'], 'style' => 'margin-bottom: -2px'));
			?>
		</td>
		<td style="width: 85px"><?php echo ($row['status_id'] == $this->hoststatus ? '<strong>' : '').html::anchor($row['url'], html::specialchars($row['lable'].' '.$row['status']), array('class' => 'status-'.strtolower($row['status']))).($row['status_id'] == $this->hoststatus ? '</strong>' : '') ?></td>
	<?php echo ($i%2 == 1) ? '</tr>' : '' ?>
	<?php	 $i++; } ?>
	<tr>
		<td class="status icon" style="padding: 4px 7px"><?php echo html::image($this->add_path('icons/12x12/shield-info.png'),array('title' => $label_all_types, 'alt' => $label_all_types, 'style' => 'margin-bottom: -2px')); ?></td>
		<td><?php echo html::anchor('status/'.$target_method.'/'.$this->host.'?'.$grouptype_arg, html::specialchars($total_hosts.' Hosts'), array('class' => 'status-total')) ?></td>
		<td class="status icon" style="padding: 4px 7px">
			<?php
				if ($total_problems > 0)
					echo html::image($this->add_path('icons/12x12/shield-warning.png'),array('title' => $label_all_problems, 'alt' => $label_all_problems, 'style' => 'margin-bottom: -2px'));
				else
					echo html::image($this->add_path('icons/12x12/shield-not-warning.png'),array('title' => $label_all_problems, 'alt' => $label_all_problems, 'style' => 'margin-bottom: -2px'));
			?>
		</td>
		<td><?php echo html::anchor('status/host/'.$this->host.'/?hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&'.$grouptype_arg, html::specialchars($total_problems.' Problems'), array('class' => 'status-warning')) ?></td>
	</tr>
</table>

<table class="w-table service-totals">
	<?php $i = 0;foreach ($service_header as $row) { ?>
	<?php echo ($i%2 == 0) ? '<tr>' : '' ?>
		<td class="status icon" style="padding: 4px 7px">
			<?php
				if ($row['lable'] > 0)
					echo html::image($this->add_path('icons/12x12/shield-'.strtolower($row['status']).'.png'), array('alt' => $row['status'],'title' => $row['status'],'style' => 'margin-bottom: -2px')) ;
				else
					echo html::image($this->add_path('icons/12x12/shield-not-'.strtolower($row['status']).'.png'), array('alt' => $row['status'],'title' => $row['status'],'style' => 'margin-bottom: -2px')) ;
			?>
		</td>
		<td style="width: 85px"><?php echo ($row['status_id'] == $this->servicestatus ? '<strong>' : '').html::anchor($row['url'], html::specialchars($row['lable'].' '.$row['status']), array('class' => 'status-'.strtolower($row['status']))).($row['status_id'] == $this->servicestatus ? '</strong>' : '') ?></td>
	<?php echo ($i%2 == 1) ? '</tr>' : ''; ?>
	<?php  $i++; } ?>
		<td class="status icon" style="padding: 4px 7px"><?php echo html::image($this->add_path('icons/12x12/shield-info.png'),array('title' => $label_all_types, 'alt' => $label_all_types, 'style' => 'margin-bottom: -2px')); ?></td>
		<td><?php echo html::anchor('status/service/'.$this->host.'/?hoststatustypes='.$this->hoststatus.'&'.$grouptype_arg, html::specialchars($svc_total_services.' Services'), array('class' => 'status-warning')) ?></td>
	</tr>
	<tr>
		<td class="status icon" style="padding: 4px 7px">
			<?php
				if ($svc_total_problems > 0)
					echo html::image($this->add_path('icons/12x12/shield-warning.png'),array('title' => $label_all_problems, 'alt' => $label_all_problems, 'style' => 'margin-bottom: -2px'));
				else
					echo html::image($this->add_path('icons/12x12/shield-not-warning.png'),array('title' => $label_all_problems, 'alt' => $label_all_problems, 'style' => 'margin-bottom: -2px'));
			?>
		</td>
		<td><?php echo html::anchor('status/service/'.$this->host.'/?hoststatustypes='.$this->hoststatus.'&servicestatustypes='.(nagstat::SERVICE_UNKNOWN|nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL).'&'.$grouptype_arg, html::specialchars($svc_total_problems.' Problems'), array('class' => 'status-warning')) ?></td>
		<td colspan="2">&nbsp;</td>
	</tr>
</table>
