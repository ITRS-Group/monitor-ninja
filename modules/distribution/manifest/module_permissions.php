<?php

$manifest = array_merge_recursive($manifest, array(
	'monitor' => array(
		'distribution' => array(
			'peers' => array(
				':read' => array()
			),
			'pollers' => array(
				':read' => array()
			)
		)
	)
));
