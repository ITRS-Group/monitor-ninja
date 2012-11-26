<?php

abstract class LivestatusFilterBase {
	abstract function generateFilter();
	abstract function prefix( $prefix );
}