<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<?php $t = $this->translate; ?>
<div class="host_breakdown wide" style="margin-top: 0px;">
<?php
	if (!isset($host_filter_status) || $host_filter_status == false) {
			$host_filter_status = array(
				'up' => 1,
				'down' => 1,
				'unreachable' => 1,
				'undetermined' => 1
			);
	}
?>

<?php echo $t->_('Showing hosts in state: ');
		$j = 0; foreach($host_filter_status as $key => $value) {
		if ($value == 1) {
			echo ($j > 0) ? ', ' : '';
			echo '<strong>'.$key.'</strong>';
			$j++;

		}
	}
	?>
<?php echo ($create_pdf) ? '<p>&nbsp;</p>' : '';?>
<?php foreach ($multiple_states as $data) { ?>
		<table summary="<?php echo $t->_('Host state breakdown') ?>" id="multiple_hosts"<?php echo ($create_pdf) ? 'style="border: 1px solid #cdcdcd;" cellpadding="5"' : '';?>>
            <tr>
				<th <?php echo ($create_pdf) ? 'style="width: 304px; font-weight: bold; background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone left" ';?>><?php echo ((!$create_pdf) ? help::render('hostgroup_breakdown') : '').' '.(!empty($data['groupname']) ? str_replace('Hostgroup:','',$data['groupname']) : 'Selected hosts'); ?></th>
				<th <?php echo ($create_pdf) ? 'style="width: 90px; font-weight: bold; text-align: right; background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone" style="width: 80px"';?>><?php echo $t->_('Up') ?></th>
				<th <?php echo ($create_pdf) ? 'style="width: 95px; font-weight: bold; text-align: right; background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone" style="width: 80px"';?>><?php echo $t->_('Unreachable') ?></th>
				<th <?php echo ($create_pdf) ? 'style="width: 90px; font-weight: bold; text-align: right; background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone" style="width: 80px"';?>><?php echo $t->_('Down') ?></th>
				<th <?php echo ($create_pdf) ? 'style="width: 105px; font-weight: bold; text-align: right; background-color: #e2e2e2; font-size: 0.9em"' : 'class="headerNone" style="width: 80px"';?>><?php echo $t->_('Undetermined') ?></th>
			</tr>
			<?php $no = 0; for ($i=0;$i<$data['nr_of_items'];$i++): ?>
			<?php if (($data['undetermined'][$i] != 0 && $host_filter_status['undetermined'] == 1) ||
						 ($data['up'][$i] != 0 && $host_filter_status['up'] == 1) ||
						 ($data['down'][$i] != 0 && $host_filter_status['down'] == 1) ||
						 ($data['unreachable'][$i] != 0 && $host_filter_status['unreachable'] == 1)) { $no++;?>
			<?php $bg_color = ($i%2 == 0) ? '#ffffff' : '#f2f2f2'; ?>
			<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd'?>">
			<?php if (!$use_alias) { ?>
				<td <?php echo ($create_pdf) ? 'style="width: 304px; font-size: 0.9em; background-color: '.$bg_color.'"' : ''; ?>><?php echo $create_pdf != false ? $data['HOST_NAME'][$i] : '<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . $data['HOST_NAME'][$i] . '</a>' ?></td>
				<?php } else { ?>	
				<td <?php echo ($create_pdf) ? 'style="width: 304px; font-size: 0.9em; background-color: '.$bg_color.'"' : ''; ?>><?php echo $this->_get_host_alias($data['HOST_NAME'][$i]) ?> (<?php echo $create_pdf != false ? $data['HOST_NAME'][$i] :'<a href="'.str_replace('&','&amp;',$data['host_link'][$i]).'">' . $data['HOST_NAME'][$i] . '</a>' ?>)</td>
				<?php } ?>
				<td <?php echo ($create_pdf) ? 'style="width: 90px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data"'; ?>><?php echo reports::format_report_value($data['up'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['up'][$i]) > 0 ? '' : 'not-').'up.png'),
							array( 'alt' => $t->_('Up'), 'title' => $t->_('Up'), 'style' => 'height: 12px; width: 12x'));
					if (isset($data['counted_as_up'][$i]) && $data['counted_as_up'][$i] > 0) {
						echo " (" . reports::format_report_value($data['counted_as_up'][$i]) ."% in other states)";
					}?></td>
				<td <?php echo ($create_pdf) ? 'style="width: 95px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data"'; ?>><?php echo reports::format_report_value($data['unreachable'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['unreachable'][$i]) > 0 ? '' : 'not-').'unreachable.png'),
							array( 'alt' => $t->_('Unreachable'), 'title' => $t->_('Unreachable'), 'style' => 'height: 12px; width: 12px')) ?></td>
				<td <?php echo ($create_pdf) ? 'style="width: 90px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data"'; ?>><?php echo reports::format_report_value($data['down'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['down'][$i]) > 0 ? '' : 'not-').'down.png'),
							array( 'alt' => $t->_('Down'), 'title' => $t->_('Down'), 'style' => 'height: 12px; width: 12px'))?></td>
				<td <?php echo ($create_pdf) ? 'style="width: 105px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data"'; ?>><?php echo reports::format_report_value($data['undetermined'][$i]) ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.(reports::format_report_value($data['undetermined'][$i]) > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $t->_('Undetermined'), 'title' => $t->_('Undetermined'), 'style' => 'height: 12px; width: 12px')) ?></td>
			</tr>
			<?php } ?>
			<?php endfor; if ($use_average==0 && $no > 0): ?>
			<?php $bg_color = ($i%2 == 0) ? '#ffffff' : '#f2f2f2'; ?>
			<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd'; $i++?>">
				<td <?php echo ($create_pdf) ? 'style="width: 304px; font-size: 0.9em; background-color: '.$bg_color.'"' : ''; ?>><?php echo $t->_('Average'); ?></td>
				<td <?php echo ($create_pdf) ? 'style="width: 90px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data"'; ?>><?php echo $data['average_up'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_up'] > 0 ? '' : 'not-').'up.png'),
							array( 'alt' => $t->_('Up'), 'title' => $t->_('Up'), 'style' => 'height: 12px; width: 12px')) ?></td>
				<td <?php echo ($create_pdf) ? 'style="width: 95px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data"'; ?>><?php echo $data['average_unreachable'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_unreachable'] > 0 ? '' : 'not-').'unreachable.png'),
							array( 'alt' => $t->_('Unreachable'), 'title' => $t->_('Unreachable'), 'style' => 'height: 12px; width: 12px')) ?></td>
				<td <?php echo ($create_pdf) ? 'style="width: 90px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data"'; ?>><?php echo $data['average_down'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_down'] > 0 ? '' : 'not-').'down.png'),
							array( 'alt' => $t->_('Down'), 'title' => $t->_('Down'), 'style' => 'height: 12px; width: 12px')) ?></td>
				<td <?php echo ($create_pdf) ? 'style="width: 105px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data"'; ?>><?php echo $data['average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $t->_('Undetermined'), 'title' => $t->_('Undetermined'), 'style' => 'height: 12px; width: 12px')) ?></td>
			</tr>
			<?php endif; if ($no > 0) { ?>
			<?php $bg_color = ($i%2 == 0) ? '#ffffff' : '#f2f2f2'; ?>
			<tr class="group-average <?php echo ($i%2 == 0) ? 'even' : 'odd'?>">
				<td <?php echo ($create_pdf) ? 'style="width: 304px; font-size: 0.9em; background-color: '.$bg_color.'"' : ''; ?>><?php echo ($use_average==0) ? $t->_('Group availability (SLA)') : $t->_('Average'); ?></td>
				<td <?php echo ($create_pdf) ? 'style="width: 90px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data_green"'; ?>bgcolor="#e3f8dc"><?php echo $data['group_average_up'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_up'] > 0 ? '' : 'not-').'up.png'),
							array( 'alt' => $t->_('Up'), 'title' => $t->_('Up'), 'style' => 'height: 12px; width: 12px')) ?></td>
				<td <?php echo ($create_pdf) ? 'style="width: 95px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data_red"'; ?>bgcolor="#ffe8e8"><?php echo $data['group_average_unreachable'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_unreachable'] > 0 ? '' : 'not-').'unreachable.png'),
							array( 'alt' => $t->_('Unreachable'), 'title' => $t->_('Unreachable'), 'style' => 'height: 12px; width: 12px')) ?></td>
				<td <?php echo ($create_pdf) ? 'style="width: 90px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data_red"'; ?>bgcolor="#ffe8e8"><?php echo $data['group_average_down'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_down'] > 0 ? '' : 'not-').'down.png'),
							array( 'alt' => $t->_('Down'), 'title' => $t->_('Down'), 'style' => 'height: 12px; width: 12px')) ?></td>
				<td <?php echo ($create_pdf) ? 'style="width: 105px; font-size: 0.9em; text-align: right; background-color: '.$bg_color.'"' : 'class="data_red"'; ?>bgcolor="#ffe8e8"><?php echo $data['group_average_undetermined'] ?> % <?php echo html::image($this->add_path('icons/12x12/shield-'.($data['group_average_undetermined'] > 0 ? '' : 'not-').'pending.png'),
							array( 'alt' => $t->_('Undetermined'), 'title' => $t->_('Undetermined'), 'style' => 'height: 12px; width: 12px')) ?></td>
			</tr>
			<?php } if ($no == 0) { ?>
			<tr class="even">
				<td colspan="5">
					<?php echo $t->_('No hosts in this group in state ');
						$j = 0; foreach($host_filter_status as $key => $value) {
						if ($value == true) {
							echo ($j > 0) ? $t->_(' or ') : '';
							echo '<strong>'.$key.'</strong>';
							$j++;

						}
					}
					?>
				</td>
			</tr>

			<?php } if (!$create_pdf) { ?>
			<tr id="pdf-hide">
				<td colspan="5" class="testcase-button"><?php echo $this->_build_testcase_form($data[';testcase;']); ?></td>
			</tr>
			<?php } ?>
		</table>
		<?php if ($create_pdf) { ?><br /><?php } ?>
<?php } ?>
</div>
