<?php

require_once ('op5/objstore.php');

/**
 * These tests exist to enforce the structure of ORM objects, to validate that
 * they are complete and behave in a consistent manner.
 */
class ORM_Complete_Test extends PHPUnit_Framework_TestCase {

	public function object_manifest_provider () {

		$manifest = ObjectPool_Model::load_table_classes();

		/* Object_Model is the only one built from the ORM Root generator
		 * and does not have the required functionality (set_by_key) for these tests,
		 * in addition it is not an object that we instantiate on its own.  */
		unset($manifest['object']);

		$this->assertGreaterThan(0, count($manifest));
		return $manifest;

	}

	/**
	 * As to not move the validation of the return value to the call-site the
	 * set_by_key function for all Pools should return an iterable Set even if
	 * that Set may be empty.
	 *
	 * @dataProvider object_manifest_provider
	 */
	public function test_set_by_key_always_returns_set ($object_model, $set_model, $pool_model) {
		$set = $pool_model::set_by_key('');
		$this->assertInstanceOf($set_model, $set);
	}

	/**
	 * MayI resource should be available for all object Sets, while ninja will
	 * allow a Set without a mayi_resource for all things except listview this
	 * test enforces this to supply a more consistent ORM.
	 *
	 * @dataProvider object_manifest_provider
	 */
	public function test_mayi_resource_available_for_all_sets ($object_model, $set_model, $pool_model) {
		$set = $pool_model::all();
		$this->assertInternalType('string', $set->mayi_resource(), "mayi_resource for '$set_model' does not supply a string namespace");
		return true;
	}

	/**
	 * There should be an ACL defined for all object-types we provide, that ACL
	 * may be as simple as a group-membership of "authenticated" for the given
	 * resource.
	 *
	 * Given a user with full authorization the authorization mayi constraint
	 * should always return true.
	 *
	 * Enforcing this supplies a more consistent ORM.
	 *
	 * @dataProvider object_manifest_provider
	 */
	public function test_mayi_resource_with_acl_using_alwaysauth ($object_model, $set_model, $pool_model) {

		$user = new User_AlwaysAuth_Model();

		Op5Auth::instance()->force_user($user);
		op5MayI::instance()->be('user', Op5Auth::instance());

		$acl_auth = new user_mayi_authorization();
		op5MayI::instance()->act_upon($acl_auth, 10);

		$set = $pool_model::all();
		$resource = $set->mayi_resource();
		$this->assertTrue(
			op5MayI::instance()->run($resource . ":read"),
			"A fully authenticated/authorized user should be able to read " .
			"'$resource', you should probably check the ACL (in " .
			"auth/hooks/user_mayi_authorization.php) or supplied " .
			"mayi_resource in '$set_model' for this resource"
		);
	}

	/**
	 * Given a unauthenticated user with no authorization the authorization
	 * mayi constraint should always return false.
	 *
	 * Enforcing this supplies a more consistent ORM.
	 *
	 * @dataProvider object_manifest_provider
	 */
	public function test_mayi_resource_with_acl_using_noauth ($object_model, $set_model, $pool_model) {

		$user = new User_NoAuth_Model();

		Op5Auth::instance()->force_user($user);
		op5MayI::instance()->be('user', Op5Auth::instance());

		$acl_auth = new user_mayi_authorization();
		op5MayI::instance()->act_upon($acl_auth, 10);

		$set = $pool_model::all();
		$resource = $set->mayi_resource();
		$this->assertFalse(
			op5MayI::instance()->run($resource . ":read"),
			"An unauthenticated/unauthorized user should not be able to read " .
			"'$resource', you should probably check the ACL (in " .
			"auth/hooks/user_mayi_authorization.php) or supplied " .
			"mayi_resource in '$set_model' for this resource"
		);
	}

}
