<?php

require_once(__DIR__."/../driver_SQL/ORMSQLObjectPoolGenerator.php");

class ORMPgSQLObjectPoolGenerator extends ORMSQLObjectPoolGenerator {
	/* We just need another visitor class for this */
	protected $visitor_class = "LivestatusPgSQLBuilderVisitor";
}
