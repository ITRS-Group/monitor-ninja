<?php

abstract class LivestatusFilterBase {
	abstract function prefix( $prefix );
	abstract function visit( LivestatusFilterVisitor $visitor, $data );
}
