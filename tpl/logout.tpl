<div class="wrap">
	<h2><?php _e($mp_defs['app_name']) ?> - Syndicate Logout</h2>
		<p><?php _e("This page allows you to logout of the {$mp_defs['app_name']} Syndicate.") ?></p>
		<p>
			<center>
				<strong>
					Do you really wish to logout?
				</strong>
					<br>
				<form method="post">
					<input type="submit" name="180_logout_do" value="Yes">
					<input type="button" onclick="self.location='<?php _e(admin_url('admin.php?page=wp_oneighty')) ?>';" value="No">
				</form>
			</center>
		</p>
</div>