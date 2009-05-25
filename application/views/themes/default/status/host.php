<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php
if (!empty($widgets)) {
	foreach ($widgets as $widget) {
		echo $widget;
	}
}
?>
<div class="widget left w32" id="page_links">
	<ul>
	<?php
	if (isset($page_links)) {
		foreach ($page_links as $label => $link) {
			?>
			<li><?php echo html::anchor($link, $label) ?></li>
			<?php
		}
	}
	?>
	</ul>
</div>

<div class="widget left w98" id="status_host">
	<!--<div id="status_msg" class="widget-header"><?php echo $sub_title ?></div>-->
	<table id="host_table" style="table-layout: fixed">
		<colgroup>
			<col style="width: 30px" />
			<col style="width: 200px" />
			<col style="width: 30px" />
			<col style="width: 122px" />
			<col style="width: 105px" />
			<col style="width: 100%" />
			<col style="width: 30px" />
			<col style="width: 30px" />
			<col style="width: 30px" />
		</colgroup>
		<thead>
			<tr>
				<th><?php echo $this->translate->_('') ?></th>
				<th colspan="2"><?php echo $this->translate->_('Host') ?></th>
				<th style="width: 100px"><?php echo $this->translate->_('Last check') ?></th>
				<th><?php echo $this->translate->_('Duration') ?></th>
				<th><?php echo $this->translate->_('Status information') ?></th>
				<th class="{sorter: false} no-sort" colspan="3"><?php echo $this->translate->_('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
		<?php

# Do not, under ANY circumstances, remove the if-clause below.
# Doing so results in a Kohana error if no hosts are found. That
# is a VERY, VERY BAD THING, so please pretty please leave it where
# it is (yes, I'm talking to you, My).
if (empty($result)) {
	$result = array();
}
$a = 0;
foreach ($result as $row) {
	$a++;
		?>
			<tr class="<?php echo ($a %2 == 0) ? 'odd' : 'even'; ?>">
				<td class="icon bl">
					<?php echo html::image('/application/views/themes/default/icons/16x16/shield-'.strtolower(Current_status_Model::status_text($row->current_state, Router::$method)).'.png',array('alt' => Current_status_Model::status_text($row->current_state, Router::$method), 'title' => $this->translate->_('Host status').': '.Current_status_Model::status_text($row->current_state, Router::$method))); ?>
				</td>
				<td>
					<?php
						echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars($row->host_name));

						if ($row->problem_has_been_acknowledged) {
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars('ACK'));
						}
						if (empty($row->notifications_enabled)) {
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars('nDIS'));
						}
						if (!$row->active_checks_enabled) {
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars('DIS'));
						}
						if (isset($row->is_flapping) && $row->is_flapping) {
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars('FPL'));
						}
						if ($row->scheduled_downtime_depth > 0) {
							echo html::anchor('extinfo/details/host/'.$row->host_name, html::specialchars('SDT'));
						}
					?>
				</td>
				<td class="icon" style="width: 10px">
				<?php if (!empty($row->icon_image)) {
					echo html::image('application/media/images/logos/'.$row->icon_image, array('style' => 'height: 16px; width: 16px', 'alt' => $row->icon_image_alt, 'title' => $row->icon_image_alt));
				} ?>
				</td>
				<td><?php echo date('Y-m-d H:i:s',$row->last_check) ?></td>
				<td><?php echo $row->duration ?></td>
				<td style="white-space	: normal"><?php echo str_replace('','',$row->plugin_output) ?></td>
				<td class="icon">
					<?php echo html::anchor('status/service/'.$row->host_name,html::image('/application/views/themes/default/icons/16x16/service-details.gif', $this->translate->_('View service details for this host')), array('style' => 'border: 0px')) ?>
				</td>
				<td class="icon">
				<?php if (!empty($row->action_url)) { ?>
					<a href="<?php echo $row->action_url ?>" style="border: 0px">
						<?php echo html::image('/application/views/themes/default/icons/16x16/host-actions.png', $this->translate->_('Perform extra host actions')) ?>
					</a>
				<?php	} if (!empty($row->notes_url)) { ?>
					<a href="<?php echo $row->notes_url ?>" style="border: 0px">
						<?php echo html::image('application/views/themes/default/icons/16x16/host-notes.png', $this->translate->_('View extra host notes')) ?>
					</a>
				<?php	} ?>
				</td>
				<td class="icon">
					<?php echo html::anchor('configuration/configure/host/'.$row->host_name, html::image('/application/views/themes/default/icons/16x16/nacoma.png',$this->translate->_('Configure this host')) )?>
				</td>
			</tr>
			<?php	} ?>
		</tbody>
	</table>
	<div id="status_count_summary"><?php echo sizeof($result).' '.$this->translate->_('Matching Host Entries Displayed'); ?></div>
	<?php echo (isset($pagination)) ? $pagination : ''; ?>
</div>