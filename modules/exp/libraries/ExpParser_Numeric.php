<?php

class ExpParser_Numeric_Core extends ExpParser_Core {
	protected function run() {
		return $this->expa();
	}
	
	protected function expa() {
		$val = $this->expb();
		do {
			$sym = $this->acceptSym( array('+','-') );
			if( $sym !== false ) {
				$r = $this->expb();
				switch( $sym ) {
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
			$sym = $this->acceptSym( array('*','/') );
			if( $sym !== false ) {
				$r = $this->expc();
				switch( $sym ) {
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
			$sym = $this->acceptSym( array('^') );
			if( $sym !== false ) {
				$r = $this->exp_fin();
				$val = pow( $val, $r );
			}
		} while( $sym !== false );
		return $val;
	}
	protected function exp_fin() {
		if( $this->acceptSym( array( '(' ) ) ) {
			$res = $this->expa();
			$this->expectSym( array( ')' ) );
			return $res;
		}
		return $this->expectNum();
	}
}