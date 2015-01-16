<?php defined('SYSPATH') OR die('No direct access allowed.');
$columns = array_diff(array_keys($options->get_alternatives('host_filter_status')), array_keys($options['host_filter_status']));

$i = 0;
foreach ($multiple_states as $data) {
	if (!is_array($data) || !isset($data['states']))
		continue;
	if (isset($data['groupname'])) {
		$groupname = array();
		foreach ($data['groupname'] as $gn) {
			if ($options['use_alias'])
				$gn = reports::get_alias('hostgroups', $gn).' ('.$gn.')';
			$groupname[] = '<a href="'.url::base(true).$type.'/generate?hostgroup%5B%5D='.$gn.'&amp;'.$options->as_keyval_string(true).'">'.$gn.'</a>';
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
		return '<td><a href="'.url::base(true).$type.'/generate?report_type=hosts&amp;objects%5B%5D='.$data['states']['HOST_NAME'].'&amp;'.$options->as_keyval_string(true).'">'.$name.'</a></td>';
	}, 'host', $columns, false, $options, $i);
	echo reports::format_multi_object_table(array($data), sprintf(_('Summary of %s'), $groupname?:_('selected hosts')), function($data) use ($options) {
		return '<td>'.$options->get_value('sla_mode').'</td>';
	}, 'host', $columns, true, $options, $i);
}
if (isset($multiple_states['groupname']) && count($multiple_states['groupname']) > 1) {
	echo reports::format_multi_object_table(array($multiple_states), _('Total summary for all hosts'), function($data) use ($options) {
		return '<td>'.$options->get_value('sla_mode').'</td>';
	}, 'host', $columns, true, $options, $i);
}
