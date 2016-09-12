<?php

require_once "op5/objstore.php";
require_once "op5/config.php";
require_once "op5/auth/Authorization.php";

/**
 * Ensures the validity of the etc/auth* files, and that they are up to date,
 * through cross referencing with the auth code in Ninja.
 */
class AuthFilesTest extends PHPUnit_Framework_TestCase {
	private $tmp_auth_groups_file;
	private $tmp_auth_file;

	public function setUp() {
		$this->tmp_auth_groups_file = __DIR__.'/auth_groups.yml';
		$copy_result = copy(__DIR__.'/../etc/auth_groups.yml', $this->tmp_auth_groups_file);
		assert('$copy_result == true');
		$this->tmp_auth_file = __DIR__.'/auth.yml';
		$copy_result = copy(__DIR__.'/../etc/auth.yml', $this->tmp_auth_file);
		assert('$copy_result == true');
	}

	public function tearDown() {
		op5objstore::instance()->mock_clear();
        $unlink_result = unlink($this->tmp_auth_groups_file);
		assert('$unlink_result == true');
		$unlink_result = unlink($this->tmp_auth_file);
		assert('$unlink_result == true');
	}

	/**
	 * Bringing you all of the auth rights that exist in Monitor, by
	 * flattening the result of op5Authorization::get_all_auth_levels().
	 *
	 * One auth right is, for example, host_view_all.
	 *
	 * @return array
	 */
	private function get_all_auth_rights() {
		$all_auth_levels_grouped = op5Authorization::get_all_auth_levels();
		$all_auth_levels = array();
		foreach($all_auth_levels_grouped as $groups) {
			$all_auth_levels = array_merge($all_auth_levels, array_keys($groups));
		}
		return $all_auth_levels;
	}

	/**
	 * When we add authorization points in Ninja, we usually add mayi
	 * rights in the code, then define it in the ACL
	 * (user_mayi_authorization), but we might forget to add the right to
	 * the list of all auth rights
	 * (op5Authorization::get_all_auth_rights()). This test makes sure that
	 * we don't forget that last step. Otherwise, the auth right cannot be
	 * configured in Nacoma.
	 */
	public function test_all_acl_groups_should_exist_in_op5_authorization() {
		$mayi_constraints = new user_mayi_authorization();
		$acl = $mayi_constraints->get_acl();
		$auth_rights_in_mayis_eyes = array_unique(
			array_map(function($acl_rule) {
				return $acl_rule[0];
			}, $acl)
		);

		// We need to remove some of the acl groups that are not
		// auth rights. Let's do that here, and if more code is
		// interested to know this introspective fact about the mayi
		// acl, then this code could be exposed within
		// user_mayi_authorization instead.
		$auth_rights_in_mayis_eyes = array_diff(
			$auth_rights_in_mayis_eyes,
			array(
				// mayi speak for "return true"
				"always",
				// mayi speak for "is logged in"
				"authenticated",
				// mayi speak for "user is logged in with
				// default auth driver"
				"own_user_change_password"
			)
		);

		$this->assertEquals(
			array(),
			array_diff($auth_rights_in_mayis_eyes, $this->get_all_auth_rights()),
			"Some ACL rights that mayi can act upon, are missing ".
			"from op5Authorization. Add them to ".
			"get_all_auth_levels(), please."
		);
	}

	/**
	 * When we add new rights, we might forget to actually hand them out.
	 * Even though the shipped, builtin "admins" group is just part of a
	 * sample config, we think it is common for customers to keep this
	 * group, or at least we want to support it. It is therefor good if
	 * that group cover all of the authorization points that we use.
	 *
	 * This forces the admins group to have both, for example,
	 * "host_view_all" and "host_view_contact" but that is a small price
	 * to pay for having this testable without any logic regarding which
	 * auth right is a subset of another.
	 */
	public function test_admins_group_should_have_access_to_all_auth_rights_that_exist() {
		$config = new op5config(array('basepath' => __DIR__."/../etc"));

		$this->assertEquals(
			array(),
			array_diff(
				$this->get_all_auth_rights(),
				$config->getConfig("auth_groups.admins")
			),
			"Some auth rights are missing from the admins group ".
			"in etc/auth_groups.yml, please add them there. Also ".
			"consider adding them to the limited_edit and ".
			"limited_view groups, just to keep them up to date"
		);
	}

	public function migrate_auth_yml_files_provider() {
		return array(
			array("auth.yml"),
			array("auth_groups.yml"),
		);
	}

	/**
	 * When we add new auth rights, they are added to the auth_groups.yml,
	 * other tests in this test suite guarantees that. Now, we should kill
	 * two bird with one stone - both verify that migrate_auth.php works,
	 * and make sure that we have run migrate_auth.php so that
	 * etc/auth_groups.yml always is checked into git in its newest form.
	 *
	 * @dataProvider migrate_auth_yml_files_provider
	 */
	public function test_auth_files_are_checked_in_after_auth_migrate($auth_file) {
		// op5config is in need of proper designing (since it assumes
		// that it must work with files, what an untestable assumption).
		// Until then, we must work around its desire and get the real
		// config files out of the way, everything else is destructive
		// and affects the system.

		// using op5config::instance() fills op5objstore with our,
		// "correct" op5config instance, so that migrate_auth.php can
		// reuse it
		$tmp_dir = dirname($this->tmp_auth_groups_file);
		$this->assertNotEquals($tmp_dir, __DIR__."/../etc",
			"A precondition failed: we must use a temporary ".
			"directory because op5config needs a complete dir ".
			"at its disposal. We do not want to upgrade the ".
			"checked in files from this test."
		);

		$config = op5config::instance(array(
			"basepath" => $tmp_dir
		));

		// the migrate auth script will execute directly.. sigh :)
		require __DIR__."/../install_scripts/migrate_auth.php";

		// let us use Spyc to transform yaml to php arrays, in order
		// to get a test output that is easy to do something about.
		$checked_in_auth = Spyc::YAMLLoad(__DIR__."/../etc/".$auth_file);
		$migrated_auth = Spyc::YAMLLoad($tmp_dir."/".$auth_file);
		$this->assertEquals(
			$migrated_auth,
			$checked_in_auth,
			"You need to update etc/$auth_file with more rights"
		);
	}
}
