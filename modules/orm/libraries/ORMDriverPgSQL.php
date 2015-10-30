<?php
/**
 * An ORM driver which is backed by PostgreSQL
 */
class ORMDriverPgSQL extends ORMDriverSQL {

	protected $sql_builder_visitor_class_name = 'LivestatusPgSQLBuilderVisitor';

}
