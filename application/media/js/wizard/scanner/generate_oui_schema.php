<?php

	$data	= file_get_contents( 'oui.txt' );
	preg_match_all("/[a-zA-Z0-9\-]+\s+\(hex\)\s+[a-zA-Z0-9 ]+/", $data, $tmp);

	$tmp = $tmp[0];
	$ouis = array();

	for ( $i = 0; $i < count($tmp); $i++ ) {

		$id = trim( $tmp[$i] );
		$id = preg_replace( "/\s{2,}/", "!!!", $id);
		$id = explode( "!!!", $id );

		$oui = array_shift($id);
		$oui = preg_replace( "/\-/", ":", $oui);
		array_shift($id);
		$company = implode(' ', $id);

		$ouis[$oui] = $company;

	}

	$registry = "";

	$registry .= "<?php\n";
	$registry .= "\$OUIS = array(\n";
	foreach ( $ouis as $oui => $company ) {
		$registry .= "\t\"$oui\" => \"$company\",\n";
	}
	$registry .= ");";

	file_put_contents("registry.php", $registry);