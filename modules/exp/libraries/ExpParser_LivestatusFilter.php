<?php

class ExpParser_LivestatusFilter_Core extends ExpParser_Core {
	protected $columns     = false;
	protected $head_and    = "And:";
	protected $head_or     = "Or:";
	protected $head_negate = "Negate:";
	protected $head_filter = "Filter:";
	
	public function setColumns( $columns ) {
		$this->columns = $columns;
	}

	public function setStats() {
		$this->head_and    = "StatsAnd:";
		$this->head_or     = "StatsOr:";
		$this->head_negate = "StatsNegate:";
		$this->head_filter = "Stats:";
	}
	
/* Expression entry point */
	protected function run() {
		return $this->bool_entry();
	}
	
/* And/Or rules */
	
	protected function bool_entry() {
		return $this->bool_or();
	}

	protected function bool_or() {
		$res = $this->bool_and();
		$count = 1;
		do {
			$sym = $this->acceptSym( array('or') );
			if( $sym !== false ) {
				$res .= $this->bool_and();
				$count++;
			}
		} while( $sym !== false );
		if( $count > 1 )
			$res .= $this->head_or . " $count\n";
		return $res;
	}
	
	protected function bool_and() {
		$res = $this->bool_expr();
		$count = 1;
		do {
			$sym = $this->acceptSym( array('and') );
			if( $sym !== false ) {
				$res .= $this->bool_expr();
				$count++;
			}
		} while( $sym !== false );
		if( $count > 1 )
			$res .= $this->head_and . " $count\n";
		return $res;
	}
	
	protected function bool_expr() {
		$neg = false;
		if( $this->acceptKeyword( array( 'not' ) ) ) {
			$neg = true;
		}
		
		if( $this->acceptSym( array( '(' ) ) ) {
			$res = $this->bool_or();
			$this->expectSym( array( ')' ) );
		} else {
			$res = $this->filter_entry();
		}
		if( $neg ) {
			$res .= $this->head_negate . "\n";
		}
		return $res;
	}
	
/* Column filter rules */
	
	protected function filter_entry() {
		$column   = $this->expectKeyword( $this->columns );

		$operator = $this->acceptSym( array('!~~','!=~','!~','~~','=~','~') );
		if( $operator !== false ) {
			$value = $this->expectString();
		} else {
			$operator = $this->expectSym( array('!>=','!<=','!>','!<','!=','>=','<=','>','<','=') );
			$value = $this->acceptString();
			if( $value === false ) {
				$value = $this->expectNum();
			}
		}
		return $this->head_filter." $column $operator $value\n";
	}
}