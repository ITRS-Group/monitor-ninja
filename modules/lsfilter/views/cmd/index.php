<?php
/* @var $object Object_Model */
/* @var $table string */
/* @var $command string */
/* @var $command_info string */
/* @var $error string */
echo "<h2>" . $command_info['name'] . " on " . $object->get_key() . "</h2>";

if (empty( $error )) {
	echo form::open( 'cmd/obj', array (
		'id' => 'command_form',
		'method' => 'post'
	) );
	echo form::hidden( 'command', $command );
	echo form::hidden( 'table', $object->get_table() );
	echo form::hidden( 'object', $object->get_key() );
	echo "<table>";
	foreach ( $command_info['param'] as $param_def ) {
		list ( $param_type, $param_name ) = $param_def;

		echo "<tr>";
		echo "<td>" . mb_convert_case(str_replace('_',' ',$param_name), MB_CASE_TITLE) . "</td>";
		echo "<td>";
		switch ($param_type) {
			case 'string' :
				echo form::input( array (
					'class' => "input-wide autotest-required",
					'name' => $param_name,
					'title' => _( 'Required field' ),
					'id' => 'field_' . $param_name
				) );
				break;
			case 'time' :
				echo form::input( array (
					'class' => "input-wide autotest-date",
					'name' => $param_name,
					'title' => _( 'Required field' ),
					'id' => 'field_' . $param_name
				) );
				break;
			case 'duration' :
				echo form::input( array (
					'class' => "input-wide autotest-float",
					'name' => $param_name,
					'title' => _( 'Required field' ),
					'id' => 'field_' . $param_name
				) );
				break;
			case 'select' :
				echo form::input( array (
					'class' => "input-wide autotest-required",
					'name' => $param_name,
					'title' => _( 'Required field' ),
					'id' => 'field_' . $param_name
				) );
				break;
			case 'bool' :
				echo form::checkbox(  array (
					'name' => $param_name,
					'id' => 'field_' . $param_name
				), true, false, 'class="checkbox"' );
				break;
		}
		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo form::submit( false, 'Submit', 'class="submit"' );
	echo form::close();
} else {
	echo "Error: " . $error;
}