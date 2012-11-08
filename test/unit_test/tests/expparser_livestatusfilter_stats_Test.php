<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
class ExpParser_LivestatusFilter_Stats_Test extends ExpParser_LivestatusFilter_Test {
	/* Runs the standard livestatus tests, but in stats mode */

	protected function run_test( $query, $expect ) {
		if( is_array( $expect ) ) {
			$expect = implode("\n",$expect)."\n";
		}
		$expect = str_replace( array(
					'Filter:',
					'And:',
					'Or:',
					'Negate:'
				),
				array(
					'Stats:',
					'StatsAnd:',
					'StatsOr:',
					'StatsNegate:'
					),
				$expect);

		$parser = new ExpParser_LivestatusFilter();
		$parser->setStats();
		$result = $parser->parse( $query );
		$this->ok_eq( $result, $expect, "SearchFilter query '$query' doesn't match expected result." );
		return $parser;
	}
}