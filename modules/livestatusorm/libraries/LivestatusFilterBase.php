<?php

abstract class LivestatusFilterBase {
	abstract function generateFilter();
	abstract function generateStats();
	abstract function prefix( $prefix );
}