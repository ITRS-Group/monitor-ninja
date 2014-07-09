<?php

/**
 * Convert a Livestatus Filter tree to a livestatus stats-query
 */
class LivestatusStatsBuilderVisitor extends LivestatusFilterBuilderVisitor {
	/**
	 * The query name of a filter
	 */
	protected $filter = "Stats: ";
	/**
	 * The query name of a and-line
	 */
	protected $and    = "StatsAnd: ";
	/**
	 * The query name of a or-line
	 */
	protected $or     = "StatsOr: ";
	/**
	 * The query name of a negation line
	 */
	protected $not    = "StatsNegate:";
}