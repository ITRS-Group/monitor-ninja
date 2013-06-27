<?php defined('SYSPATH') OR die('No direct access allowed.');
$columns = array('up', 'down', 'unreachable', 'pending');
foreach (array_keys($options['host_filter_status']) as $filtered)
	unset($columns[array_search(Reports_Model::$host_states[$filtered], $columns)]);
$i = 0;
foreach ($multiple_states as $data) {
	if (!is_array($data) || !isset($data['states']))
		continue;
	if (isset($data['groupname'])) {
		$groupname = array();
		foreach ($data['groupname'] as $gn) {
			if ($options['use_alias'])
				$gn = reports::get_alias('hostgroups', $gn).' ('.$gn.')';
			$groupname[] = '<a href="'.url::base(true).$type.'/generate?hostgroup[]='.$gn.'&amp;'.$options->as_keyval_string(true).'">'.$gn.'</a>';
		}
		$groupname = implode(', ', $groupname);
	} else {
		$groupname = false; # Because capitalization
	}
	echo reports::format_multi_object_table($data, $groupname?:'Selected hosts', function($data) use ($options, $type) {
		if ($options['use_alias'])
			$name = reports::get_alias('hosts', $data['states']['HOST_NAME']).' ('.$data['states']['HOST_NAME'].')';
		else
			$name = $data['states']['HOST_NAME'];
		return '<td><a href="'.url::base(true).$type.'/generate?host_name[]='.$data['states']['HOST_NAME'].'&amp;'.$options->as_keyval_string(true).'">'.$name.'</a></td>';
	}, $columns, false, $options['scheduleddowntimeasuptime'] == 2, $i);
	echo reports::format_multi_object_table(array($data), sprintf(_('Summary of %s'), $groupname?:_('selected hosts')), function($data) use ($options) {
		return '<td>'.$options->get_value('sla_mode').'</td>';
	}, $columns, true, $options['scheduleddowntimeasuptime'] == 2, $i);
}
if (isset($multiple_states['groupname']) && count($multiple_states['groupname']) > 1) {
	echo reports::format_multi_object_table(array($multiple_states), _('Total summary for all hosts'), function($data) use ($options) {
		return '<td>'.$options->get_value('sla_mode').'</td>';
	}, $columns, true, $options['scheduleddowntimeasuptime'] == 2, $i);
}
