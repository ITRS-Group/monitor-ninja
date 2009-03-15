<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<?php if (isset($user_list)) { ?>
<table cellpadding="2" cellspacing="0">
<thead>
	<tr>
		<th><?php echo $realname ?></th>
		<th><?php echo $username ?></th>
		<th><?php echo $access ?></th>
	</tr>
</thead>
<tbody>
<?php	foreach ($user_list as $user) {	?>
	<tr>
		<td><?php echo $user->realname ?></td>
		<td><?php echo $user->username ?></td>
		<td><?php
		# this is ugly as it shouldn't need another query
		# just to find user access
			unset($roles);
			unset($is_admin);
			$user_roles = ORM::factory('user', $user->id)->roles;
			foreach ($user_roles as $role) {
				if ($role->name == Ninja_Controller::ADMIN) {
					echo Ninja_Controller::ADMIN;
					break;
				}
			}
			#echo is_array($roles) ? implode(', ', $roles) : "";
		?></td>
	</tr>
<?php 	} ?>
</tbody>
</table>
<?php }

#$user_list = ORM::factory('user', 1)->roles;
#$user_roles = ORM::factory('user', $user->id)->roles;
#echo Kohana::debug($user_roles);
#foreach ($user_roles as $role) {
#	echo $role->name."<br />";
	#echo Kohana::debug($user);
#}

# print logout link
echo "<ul><li>".html::anchor('default/logout', html::specialchars('logout'))."</li></ul>";
?>