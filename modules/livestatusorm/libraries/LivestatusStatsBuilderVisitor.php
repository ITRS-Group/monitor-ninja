<?php

class LivestatusStatsBuilderVisitor extends LivestatusFilterBuilderVisitor {
	protected $filter = "Stats: ";
	protected $and    = "StatsAnd: ";
	protected $or     = "StatsOr: ";
	protected $not    = "StatsNegate:";
}