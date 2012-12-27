<?php

interface LivestatusFilterVisitor {
	public function visit_and( LivestatusFilterAnd $filt, $data );
	public function visit_or( LivestatusFilterOr $filt, $data );
	public function visit_match( LivestatusFilterMatch $filt, $data );
	public function visit_not( LivestatusFilterNot $filt, $data );
}
