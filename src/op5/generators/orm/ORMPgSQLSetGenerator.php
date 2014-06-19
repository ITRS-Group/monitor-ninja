<?php

require_once("ORMSQLSetGenerator.php");

class ORMPgSQLSetGenerator extends ORMSQLSetGenerator {
	/* We just need another visitor class for this */
	protected $visitor_class = "LivestatusPgSQLBuilderVisitor";
}