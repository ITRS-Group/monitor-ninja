<?php

require_once( '../buildlib.php' );

require_once( NINJA_SDK_PATH.'/orm/ORMBuilder.php' );

class orm_generator extends generator_module {
	protected function do_run() {
		require( 'structure.php' ); /* Sets $tables */

		$builder = new ORMBuilder();

		foreach( $tables as $name => $structure ) {
			$builder->generate_table($name, $tables);
		}

		$builder->generate_manifest( $tables );
	}
}

$generator = new orm_generator('monitoring');
$result = $generator->run();

if ($result != 0)
	exit($result);

exit(0);
