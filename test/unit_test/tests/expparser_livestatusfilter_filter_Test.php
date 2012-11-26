<?php defined('SYSPATH') OR die('No direct access allowed.');
require_once( 'expparser_livestatusfilter_TestBase.php' );

/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
class ExpParser_LivestatusFilter_Filter_Test extends ExpParser_LivestatusFilter_TestBase {
	/* Runs the standard livestatus tests, but in stats mode */

	protected function run_test( $query, $expect ) {
		if( is_array( $expect ) ) {
			$expect = implode("\n",$expect)."\n";
		}

		$parser = new ExpParser_LivestatusFilter();
		$result = $parser->parse( $query );
		$this->ok_eq( $result, $expect, "SearchFilter query '$query' doesn't match expected result." );
		return $parser;
	}
}