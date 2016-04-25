<div id="profile" class="profile menu">
	<ul>
	<li class="profile-info">
		<div class="profile-image">
			<img src="<?php echo $avatar; ?>">
		</div>
		<div class="profile-name">
<?php
		$lp = LinkProvider::factory();
		$username = html::specialchars($user->get_display_name());
		echo $username;
		echo "<div class=\"profile-host\">  at " . html::specialchars($host) . "</div>";
?>
		</div>
		<ul>
			<li>
				<a href="<?php echo $lp->get_url('user'); ?>">
					<span>My Account</span>
				</a>
			</li>
			<li>
				<a href="<?php echo $lp->get_url('auth', 'logout'); ?>">
					<span>Log out</span>
				</a>
			</li>
		</ul>
	</li>
	</ul>
</div>
