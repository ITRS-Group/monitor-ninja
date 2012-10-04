<?php

class ExpParser_SearchFilter_Core extends ExpParser_LivestatusFilter_Core {
	/* Expression entry point */
	protected function run() {
		$filter = "";
		if( $this->acceptSym( array( '[' ) ) ) {
			$filter .= "GET ".$this->expectKeyword()."\n";
			$this->expectSym( array( ']' ) );
		}
		
		$filter .= $this->bool_entry();
		
		while( $this->acceptSym( array(';') ) )  {
			$this->setStats();
			$filter .= $this->bool_entry();
		}
		
		return $filter;
	}
}