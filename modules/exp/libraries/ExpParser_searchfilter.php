<?php

class ExpParser_SearchFilter_Core extends ExpParser_Core {
	protected function expression() {
		return $this->expa();
	}

	protected function expa() {
		$val = $this->expb();
		do {
			$sym = $this->accept( array('op','+') );
			if( $sym === false )
				$sym = $this->accept( array('op','-') );
			if( $sym !== false ) {
				$r = $this->expb();
				switch( $sym[1] ) {
					case '+': $val += $r; break;
					case '-': $val -= $r; break;
				}
			}
		} while( $sym !== false );
		return $val;
	}
	protected function expb() {
		$val = $this->expc();
		do {
			$sym = $this->accept( array('op','*') );
			if( $sym === false )
				$sym = $this->accept( array('op','/') );
			if( $sym !== false ) {
				$r = $this->expc();
				switch( $sym[1] ) {
					case '*': $val *= $r; break;
					case '/': $val /= $r; break;
				}
			}
		} while( $sym !== false );
		return $val;
	}
	protected function expc() {
		$val = $this->exp_fin();
		do {
			$sym = $this->accept( array('op','^') );
			if( $sym !== false ) {
				$r = $this->exp_fin();
				$val = pow( $val, $r );
			}
		} while( $sym !== false );
		return $val;
	}
	protected function exp_fin() {
		if( $this->accept( array( 'op', '(' ) ) ) {
			$res = $this->expa();
			$this->expect( array( 'op', ')' ) );
			return $res;
		}
		if( $sym = $this->expect( array( 'num' ) ) ) {
			return $sym[1];
		}
	}
}