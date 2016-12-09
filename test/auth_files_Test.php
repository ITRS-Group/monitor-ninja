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
	private $preexisting_rights = array();

	public function setUp() {
		$this->tmp_auth_groups_file = __DIR__.'/auth_groups.yml';
		$copy_result = copy(__DIR__.'/../etc/auth_groups.yml', $this->tmp_auth_groups_file);
		assert('$copy_result == true');
		$this->tmp_auth_file = __DIR__.'/auth.yml';
		$copy_result = copy(__DIR__.'/../etc/auth.yml', $this->tmp_auth_file);
		assert('$copy_result == true');

		// these are the rights that existed from before migrate_auth.php
		// ever existed
		$this->preexisting_rights = array(
			'system_information',
			'configuration_information',
			'system_commands',
			'api_config',
			'api_report',
			'api_status',
			'api_perfdata',
			'host_add_delete',
			'host_view_all',
			'host_view_contact',
			'host_edit_all',
			'host_edit_contact',
			'test_this_host',
			'host_template_add_delete',
			'host_template_view_all',
			'host_template_edit_all',
			'service_add_delete',
			'service_view_all',
			'service_view_contact',
			'service_edit_all',
			'service_edit_contact',
			'test_this_service',
			'service_template_add_delete',
			'service_template_view_all',
			'service_template_edit_all',
			'hostgroup_add_delete',
			'hostgroup_view_all',
			'hostgroup_view_contact',
			'hostgroup_edit_all',
			'hostgroup_edit_contact',
			'servicegroup_add_delete',
			'servicegroup_view_all',
			'servicegroup_view_contact',
			'servicegroup_edit_all',
			'servicegroup_edit_contact',
			'hostdependency_add_delete',
			'hostdependency_view_all',
			'hostdependency_edit_all',
			'servicedependency_add_delete',
			'servicedependency_view_all',
			'servicedependency_edit_all',
			'hostescalation_add_delete',
			'hostescalation_view_all',
			'hostescalation_edit_all',
			'serviceescalation_add_delete',
			'serviceescalation_view_all',
			'serviceescalation_edit_all',
			'contact_add_delete',
			'contact_view_contact',
			'contact_view_all',
			'contact_edit_contact',
			'contact_edit_all',
			'contact_template_add_delete',
			'contact_template_view_all',
			'contact_template_edit_all',
			'contactgroup_add_delete',
			'contactgroup_view_contact',
			'contactgroup_view_all',
			'contactgroup_edit_contact',
			'contactgroup_edit_all',
			'timeperiod_add_delete',
			'timeperiod_view_all',
			'timeperiod_edit_all',
			'command_add_delete',
			'command_view_all',
			'command_edit_all',
			'test_this_command',
			'export',
			'configuration_all',
			'wiki',
			'wiki_admin',
			'nagvis_add_delete',
			'nagvis_view',
			'nagvis_edit',
			'nagvis_admin',
			'FILE',
			'access_rights',
			'pnp',
			'saved_filters_global',
		);
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

	private function check_traps_view_all_rights($user_groups) {
		$authmod = Auth::instance();

		$authmod->force_user(new User_Model( array (
			'username' => 'monitor',
			'groups' => array (
				$user_groups
			)
		) ), false );

		if($authmod->authorized_for('traps_view_all')) {
			$this->assertTrue($authmod->authorized_for('traps_view_all'));
		}else {
			$this->assertFalse($authmod->authorized_for('traps_view_all'));
			$authmod->force_user($user = new User_AlwaysAuth_Model(), false);
			$user->set_authorized_for('traps_view_all', true);
			$this->assertTrue(op5auth::instance()->authorized_for('traps_view_all'));
		}
	}

	public function test_auth_rights_based_user_roles() {
		//'admins'
		$this->check_traps_view_all_rights('admins');
		//guest
		$this->check_traps_view_all_rights('guest');
		//limited_edit
		$this->check_traps_view_all_rights('limited_edit');
		//limited_view
		$this->check_traps_view_all_rights('limited_view');
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
		$this->assertNotEquals(__DIR__."/../etc", $tmp_dir,
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

	public function test_migrate_auth_script_has_no_internal_duplicates() {
		// the migrate auth script will execute directly.. sigh :)
		require __DIR__."/../install_scripts/migrate_auth.php";
		$this->assertInternalType("array", $new_rights,
			"Failed a safety check"
		);
		var_dump("calle1", $this->preexisting_rights, $new_rights, array_intersect(
				$this->preexisting_rights,
				$new_rights
			));
		$this->assertEquals(
			array(),
			array_intersect(
				$this->preexisting_rights,
				$new_rights
			),
			'Look over $preexisting_rights and $new_rights in '.
			'migrate_auth.php, they should contain exclusive elements'
		);
	}

	public function test_migrate_auth_script_does_not_forget_any_rights() {
		// the migrate auth script will execute directly.. sigh :)
		require __DIR__."/../install_scripts/migrate_auth.php";
		$this->assertInternalType("array", $new_rights,
			"Failed a safety check"
		);

		$all_rights = $this->preexisting_rights;
		foreach($new_rights as $complete_new_right) {
			foreach($complete_new_right as $old_right => $provided_right) {
				if(is_array($provided_right)) {
					$all_rights = array_merge(
						$all_rights,
						$provided_right
					);
				} else {
					$all_rights[] = $provided_right;
				}
			}
		}

		$this->assertEquals(
			array(),
			array_diff(
				$this->get_all_auth_rights(),
				$all_rights
			),
			"Some auth right(s) should be added to migrate_auth.php."
		);
	}
}
