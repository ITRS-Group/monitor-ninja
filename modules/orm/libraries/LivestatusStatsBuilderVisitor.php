<?php

/**
 * Convert a Livestatus Filter tree to a livestatus stats-query
 */
class LivestatusStatsBuilderVisitor extends LivestatusFilterBuilderVisitor {
	protected $filter = "Stats: ";
	protected $and    = "StatsAnd: ";
	protected $or     = "StatsOr: ";
	protected $not    = "StatsNegate:";
}