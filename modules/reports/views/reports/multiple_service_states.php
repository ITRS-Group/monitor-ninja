<?php defined('SYSPATH') OR die('No direct access allowed.');
$columns = array_diff(array_keys($options->get_alternatives('service_filter_status')), array_keys($options['service_filter_status']));

foreach ($multiple_states as $data) {
	if (!is_array($data) || !isset($data['states']))
		continue;
	if (isset($data['groupname'])) {
		$groupname = array();
		foreach ($data['groupname'] as $gn) {
			if ($options['use_alias'])
				$gn = reports::get_alias('servicegroups', $gn).' ('.$gn.')';
			$groupname[] = '<a href="'.url::base(true).$type.'/generate?servicegroup[]='.$gn.'&amp;'.$options->as_keyval_string(true).'">'.$gn.'</a>';
		}
		$groupname = implode(', ', $groupname);
	} else {
		$groupname = false; # Because capitalization
	}

	$previous_hostname = false;
	echo reports::format_multi_object_table($data, $groupname?:'Selected services', function($data) use ($options, $type, &$previous_hostname, $hide_host, &$i) {
		$res = '';
		if (!$hide_host && $data['states']['HOST_NAME'] != $previous_hostname) {
			if ($options['use_alias'])
				$name = reports::get_alias('hosts', $data['states']['HOST_NAME']).' ('.$data['states']['HOST_NAME'].')';
			else
				$name = $data['states']['HOST_NAME'];
			$res .= '<td colspan="6" class="multiple label"><strong>'. _('Services on host') .'</strong>: <a href="'.url::base(true).$type.'/generate?report_type=hosts&amp;objects%5B%5D='.$data['states']['HOST_NAME'].'&amp;'.$options->as_keyval_string(true).'">'.$name.'</a></td></tr><tr class="'.($i++%2?'even':'odd').'">';
			$previous_hostname = $data['states']['HOST_NAME'];
		}
		$name = $data['states']['SERVICE_DESCRIPTION'];
		return $res.'<td><a href="'.url::base(true).$type.'/generate?report_type=services&amp;objects%5B%5D='.$data['states']['HOST_NAME'].';'.$data['states']['SERVICE_DESCRIPTION'].'&amp;'.$options->as_keyval_string(true).'">'.$name.'</a></td>';
	}, 'service', $columns, false, $options['scheduleddowntimeasuptime'] == 2, $i);
	echo reports::format_multi_object_table(array($data), sprintf(_('Summary of %s'), $groupname?:_('selected services')), function($data) use ($options) {
		return '<td>'.$options->get_value('sla_mode').'</td>';
	}, 'service', $columns, true, $options['scheduleddowntimeasuptime'] == 2, $i);
}

if (isset($multiple_states['groupname']) && count($multiple_states['groupname']) > 1) {
	echo reports::format_multi_object_table(array($multiple_states), _('Total summary for all services'), function($data) use ($options) {
		return '<td>'.$options->get_value('sla_mode').'</td>';
	}, 'service', $columns, true, $options['scheduleddowntimeasuptime'] == 2, $i);
}
