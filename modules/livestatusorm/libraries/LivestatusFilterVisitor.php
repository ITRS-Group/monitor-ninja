<?php

interface LivestatusFilterVisitor {
	public function visit_and( LivestatusFilterAnd $filt );
	public function visit_or( LivestatusFilterOr $filt );
	public function visit_match( LivestatusFilterMatch $filt );
	public function visit_not( LivestatusFilterNot $filt );
}