<?php

	require_once( "registry.php" );

	define( 'INCLI', ( PHP_SAPI === 'cli' ) );
	define( 'ADDRESS', 0 );
	define( 'PORT', 1 );

	class Scan {

		public static function mac( $address ) {

			global $OUIS;

			#	The ARPING usage restrict discovery 
			#	to the subnet of the poller 

			exec( "arping -f -w 5 $address", $result );
			$result = $result[1];

			preg_match ('/\[([a-zA-Z0-9:]+)\]/i', $result, $mac);
			$mac = $mac[1];

			$oui = substr($mac, 0, 8);

			$registrar = "Unknown";

			if ( isset($OUIS[$oui]) ) {
				$registrar = $OUIS[$oui];
			}

			return array(
				"HWADDR" => $mac,
				"REGISTRAR" => $registrar
			);

		}

		public static function exists ( $address ) {

			$command = "/usr/bin/nmap -T5 -sS -p 0 $address";
			passthru( $command, $result );

			if ( INCLI ) {
				var_dump( $result );
				echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
			}

		}

		public static function port ( $address, $port = 80, $settings = false ) {

			if ( !$settings ) {
				$settings = array();
			}

			$command = "/usr/bin/nmap -T5 -P0 -sS -p $port $address";
			exec( $command, $result );
			preg_grep( "/$port\/tcp/i" , $result, $grepped);

			if ( INCLI ) {
				var_dump( $grepped );
				echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
			}

		}

	}

	if ( INCLI ) {
		$input		= explode( ":", $argv[1] );
		$address	= $input[ ADDRESS ];
		$port		= $input[ PORT ];
	} else {
		$input		= explode( ":", $_GET['HOST'] );
		$address	= $input[ ADDRESS ];
		$port		= $input[ PORT ];
	}

	//Scan::exists( $address );
	//Scan::port( $address, $port );
	var_dump( Scan::mac( $address ) );
