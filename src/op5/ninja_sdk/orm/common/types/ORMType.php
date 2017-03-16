<?php

/**
 * Describes what is needed from an ORMType object in order to generate a type
 * implementation.
 *
 * Currently bulkier than need be because of the implicit LSrelation type which
 * requires special handling.
 */
interface ORMTypeI {

	function __construct ($name, $backend_name, $options);

	/**
	 * Retrieve the default value of the type in a stringified version
	 *
	 * @return string
	 */
	function get_default_value ();

	/**
	 * How to generate the setter for the field this type represents
	 *
	 * @param $context ORMGenerator - The Context of the currently generated class
	 * @return
	 */
	function generate_set ($context);

	/**
	 * How to generate the getter for the field this type represents
	 *
	 * @param $context ORMGenerator - The Context of the currently generated class
	 * @return
	 */
	function generate_get ($context);

	/**
	 * How to generate the save procedure
	 *
	 * @param $context ORMGenerator - The Context of the currently generated class
	 * @return
	 */
	function generate_save ($context);

	/**
	 * How to generate the setting from the set iterator factory, iterator_set
	 * differs from array_set in that it utilizes backend names instead of
	 * frontend names when used.
	 *
	 * @param $context ORMGenerator - The Context of the currently generated class
	 * @return
	 */
	function generate_iterator_set ($context);

	/**
	 * How to generate the setting from the array factory, array_set differs
	 * from iterator_set in that it utilizes frontend names instead of backend
	 * names when used.
	 *
	 * @param $context ORMGenerator - The Context of the currently generated class
	 * @return
	 */
	function generate_array_set ($context);
}

class ORMTypeFactory {

	public static function factory ($field, $type, $structure) {

		$backend_name = $field;
		if(isset($structure['rename']) && isset($structure['rename'][$field])) {
			$backend_name = $structure['rename'][$field];
		}

		$typename = $type;
		$options = $type;
		if (is_array($typename)) {
			$typename = $typename[0];
		}

		$classname = "ORMType$typename";
		if (!is_array($options)) {
			$options = array($options);
		}

		if (class_exists($classname)) {
			return new $classname($field, $backend_name, $options);
		} elseif (is_array($options)) {
			/**
			 * Legacy fallback to LSRelation as the only "complex" type
			 * Should be removed once all models have been updated to use the "relation" type
			 */
			return new ORMTypeLSRelation($field, $backend_name, $options);
		}

		throw new ORMGeneratorException("ORMType '$typename' does not exist!");

	}
}

require_once('ORMTypeInt.php');
require_once('ORMTypeFloat.php');
require_once('ORMTypeString.php');
require_once('ORMTypeBool.php');
require_once('ORMTypeList.php');
require_once('ORMTypePassword.php');
require_once('ORMTypeTime.php');
require_once('ORMTypeDict.php');

require_once('ORMTypeFlags.php');
require_once('ORMTypeSet.php');
require_once('ORMTypeRelation.php');

# This is legacy
require_once('ORMTypeLSRelation.php');
