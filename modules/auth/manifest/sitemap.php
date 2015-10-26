<?php
$manifest = array_merge_recursive($manifest, array(
	"manage" => array(
		"label" => "Manage",
		"children" => array(
			"permissions" => array(
				"label" => "Permissions",
				"children" => array(
					"nagvis_permissions" => array(
						"icon" => "nagvis",
						"label" => "Nagvis permissions",
						"right" => "ninja.configuration:read",
						"description" => "Permissions for NagVis access",
						"href" => array('configuration', 'configure', array('page' => 'nagvisls.php'))
					),
					"local_users" => array(
						"icon" => "access-config",
						"label" => "Local users",
						"right" => "ninja.configuration:read",
						"description" => "Local users stored on this server",
						"href" => array('configuration', 'configure', array('page' => 'edit_special.php/access'))
					),
					"auth_modules" => array(
						"icon" => "auth-modules",
						"label" => "Authentication modules",
						"right" => "ninja.configuration:read",
						"description" => "Ways to authenticate toward op5 Monitor",
						"href" => array('configuration', 'configure', array('page' => 'authconfig.php'))
					),
					"group_rights" => array(
						"icon" => "assign-group-rights",
						"label" => "Group rights",
						"right" => "ninja.configuration:read",
						"description" => "User roles within op5 Monitor",
						"href" => array('configuration', 'configure', array('page' => 'edit_special.php/group'))
					)
				)
			)
		)
	)
));