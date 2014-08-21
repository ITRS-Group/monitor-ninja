<?php

require_once(__DIR__."/../driver_SQL/ORMSQLObjectSetGenerator.php");

class ORMPgSQLObjectSetGenerator extends ORMSQLObjectSetGenerator {
	/* We just need another visitor class for this */
	protected $visitor_class = "LivestatusPgSQLBuilderVisitor";
}