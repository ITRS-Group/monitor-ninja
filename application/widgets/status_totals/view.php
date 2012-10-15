<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<table class="w-table width-50 left">

<?php if( $host_header !== false ) {?>
<caption>Host Totals</caption>
<?php $i=0; foreach ($host_header as $row) { ?>
	<?php echo ($i%2 == 0) ? '<tr>' : '' ?>
		
		<td class="status">
			<?php
			if ($row['lable'] > 0)
				echo '<span class="icon-12 x12-shield-'.strtolower($row['status']).'" title="'.$row['status'].'"></span>';
			else
				echo '<span class="icon-12 x12-shield-not-'.strtolower($row['status']).'" title="'.$row['status'].'"></span>';
			?>
		</td>
		<td><?php echo ($row['status_id'] == $this->hoststatus ? '<strong>' : '').html::anchor($row['url'], html::specialchars($row['lable'].' '.$row['status']), array('class' => 'status-'.strtolower($row['status']))).($row['status_id'] == $this->hoststatus ? '</strong>' : '') ?></td>
	<?php echo ($i%2 == 1) ? '</tr>' : '' ?>
	<?php	 $i++; } ?>
	<tr>
		<td class="status">
			<?php 
				echo '<span class="icon-12 x12-shield-info" title="'.$label_all_types.'">'; ?>
			?>
		</td>
		<td><?php echo html::anchor('status/'.$target_method.'/'.$this->host.'?'.$grouptype_arg, html::specialchars($hosts->total.' Hosts'), array('class' => 'status-total')) ?></td>
		<td class="status">
			<?php
				if (($hosts->down + $hosts->unreachable) > 0)
					echo '<span class="icon-12 x12-shield-warning" title="'.$label_all_problems.'"></span>';
				else
					echo '<span class="icon-12 x12-shield-not-warning" title="'.$label_all_problems.'"></span>';
			?>
		</td>
		<td><?php echo html::anchor('status/host/'.$this->host.'/?hoststatustypes='.(nagstat::HOST_DOWN|nagstat::HOST_UNREACHABLE).'&'.$grouptype_arg, html::specialchars(($hosts->down + $hosts->unreachable).' Problems'), array('class' => 'status-warning')) ?></td>
	</tr>
</table>
<?php } ?>
<table class="w-table width-50 left">
	<caption>Service Totals</caption>
	<?php $i = 0;foreach ($service_header as $row) { ?>
	<?php echo ($i%2 == 0) ? '<tr>' : '' ?>
		<td class="status">
			<?php
				if ($row['lable'] > 0)
				echo '<span class="icon-12 x12-shield-'.strtolower($row['status']).'" title="'.$row['status'].'"></span>';
			else
				echo '<span class="icon-12 x12-shield-not-'.strtolower($row['status']).'" title="'.$row['status'].'"></span>';
			?>
		</td>
		<td><?php echo ($row['status_id'] == $this->servicestatus ? '<strong>' : '').html::anchor($row['url'], html::specialchars($row['lable'].' '.$row['status']), array('class' => 'status-'.strtolower($row['status']))).($row['status_id'] == $this->servicestatus ? '</strong>' : '') ?></td>
	<?php echo ($i%2 == 1) ? '</tr>' : ''; ?>
	<?php  $i++; } ?>
		<td class="status">
			<?php			
				echo '<span class="icon-12 x12-shield-info" title="'.$label_all_types.'">'; ?>
			</td>
		<td><?php echo html::anchor('status/service/'.$this->host.'/?hoststatustypes='.$this->hoststatus.'&'.$grouptype_arg, html::specialchars($services->total.' Services'), array('class' => 'status-total')) ?></td>
	</tr>
	<tr>
		<td class="status">
			<?php
				if (($services->critical + $services->warning + $services->unknown) > 0)
					echo '<span class="icon-12 x12-shield-warning" title="'.$label_all_problems.'"></span>';
				else
					echo '<span class="icon-12 x12-shield-not-warning" title="'.$label_all_problems.'"></span>';
			?>
		</td>
		<td><?php echo html::anchor('status/service/'.$this->host.'/?hoststatustypes='.$this->hoststatus.'&servicestatustypes='.(nagstat::SERVICE_UNKNOWN|nagstat::SERVICE_WARNING|nagstat::SERVICE_CRITICAL).'&'.$grouptype_arg, html::specialchars(($services->critical + $services->warning + $services->unknown).' Problems'), array('class' => 'status-warning')) ?></td>
		<td colspan="2">&nbsp;</td>
	</tr>
</table>
<div class="clear"></div>
