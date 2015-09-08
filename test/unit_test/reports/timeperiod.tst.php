<?php
$testcase = array(
	'description' => 'Reports using timeperiods',
	'logfile' => 'softstates.log',
	'global_vars' => array(
		'start_time' => strtotime("2008-02-11 00:00:00"),
		'end_time' => strtotime("2008-02-12 00:00:00"),
		'report_type' => 'hosts',
		'objects' => array(
			0 => 'testhost'
		),
		'timeperiod' => array(
			'name' => 'an_exclude',
			'exceptions_calendar_dates' => array(
				array(
					'syear' => 2008,
					'smon' => 1,
					'smday' => 11,
					'eyear' => 2008,
					'emon' => 1,
					'emday' => 11,
					'skip_interval' => 0,
					'times' => array( // 22:00-23:30
						79200,
						84600
					)
				)
			)
		)
	),
	'monday lunchtime 12:00-13:00' => array(
		'timeperiod' => array(
			'days' => array(
				array(),
				array( // 12:00-14:00
					43200,
					46800
				),
				array(),
				array(),
				array(),
				array(),
				array()
			)
		),
		'correct' => array(
			'TIME_UP_UNSCHEDULED' => '3600',
			'TIME_INACTIVE' => '82800'
		)
	),
	'simple timeperiod' => array(
		'timeperiod' => array(
			'days' => array(
				array(),
				array( // 02:15-03:25
					8100,
					12300
				),
				array(),
				array(),
				array(),
				array(),
				array()
			)
		),
		'correct' => array(
			'TIME_UP_UNSCHEDULED' => '4200',
			'TIME_INACTIVE' => '82200',
			'TOTAL_TIME_ACTIVE' => '4200'
		)
	),
	'slightly tricksier timeperiod' => array(
		'timeperiod' => array(
			'days' => array(
				array(),
				array( // 02:15-03:25,04:15-05:25
					8100,
					12300,
					15300,
					19500
				),
				array(),
				array(),
				array(),
				array(),
				array()
			)
		),
		'correct' => array(
			'TIME_UP_UNSCHEDULED' => '8400',
			'TIME_INACTIVE' => '78000',
			'TOTAL_TIME_ACTIVE' => '8400'
		)
	),
	'tricksy timeperiod indeed' => array(
		'timeperiod' => array(
			'days' => array(
				array(),
				array( // 00:15-00:25,01:15-01:25,02:15-02:25,03:15-03:25,04:00-05:00
					900,
					1500,
					4500,
					5100,
					8100,
					8700,
					11700,
					12300,
					14400,
					18000
				),
				array(),
				array(),
				array(),
				array(),
				array()
			)
		),
		'correct' => array(
			'TIME_UP_UNSCHEDULED' => '6000',
			'TIME_INACTIVE' => '80400',
			'TOTAL_TIME_ACTIVE' => '6000'
		)
	),
	'timeperiod with simple exception' => array(
		'timeperiod' => array(
			'days' => array(
				array(),
				array( // 00:00-24:00
					0,
					86400
				),
				array(),
				array(),
				array(),
				array(),
				array()
			),
			'exceptions_calendar_dates' => array(
				array(
					'syear' => 2008,
					'smon' => 1, // Mon is 0-11 according to livestatus
					'smday' => 11,
					'eyear' => 2008,
					'emon' => 1, // Mon is 0-11 according to livestatus
					'emday' => 11,
					'skip_interval' => 0,
					'times' => array( // 22:00-23:30
						79200,
						84600
					)
				)
			)
		),
		'correct' => array(
			'TIME_UP_UNSCHEDULED' => '4200',
			'TIME_DOWN_UNSCHEDULED' => '1200',
			'TIME_INACTIVE' => '81000'
		)
	),
	'timeperiod with simple exception and three days' => array(
		'start_time' => '1202598000',
		'end_time' => '1202857200',
		'timeperiod' => array(
			'days' => array(
				array(),
				array( // 00:00-24:00
					0,
					86400
				),
				array(),
				array(),
				array(),
				array(),
				array()
			),
			'exceptions_calendar_dates' => array(
				array(
					'syear' => 2008,
					'smon' => 1, // Mon is 0-11 according to livestatus
					'smday' => 11,
					'eyear' => 2008,
					'emon' => 1, // Mon is 0-11 according to livestatus
					'emday' => 11,
					'skip_interval' => 0,
					'times' => array( // 22:00-23:30
						79200,
						84600
					)
				)
			)
		),
		'correct' => array(
			'TIME_UP_UNSCHEDULED' => '4200',
			'TIME_DOWN_UNSCHEDULED' => '1200',
			'TIME_INACTIVE' => '253800'
		)
	),
	'timeperiod with simple excludes' => array(
		'timeperiod' => array(
			'days' => array(
				array(),
				array( // 00:00-24:00
					0,
					86400
				),
				array(),
				array(),
				array(),
				array(),
				array()
			),
			'exclusions' => array(
				'an_exclude'
			)
		),
		'correct' => array(
			'TIME_UP_UNSCHEDULED' => '79200',
			'TIME_DOWN_UNSCHEDULED' => '1800',
			'TIME_INACTIVE' => '5400'
		)
	),
	'another timeperiod' => array(
		'timeperiod' => array(
			'days' => array(
				array(),
				array( // 23:00-23:30
					82800,
					84600
				),
				array(),
				array(),
				array(),
				array(),
				array()
			)
		),
		'correct' => array(
			'TIME_UP_UNSCHEDULED' => '600',
			'TIME_DOWN_UNSCHEDULED' => '1200',
			'TIME_INACTIVE' => '84600'
		)
	)
);
