<div class="wrap">
	<h2><?php _e($mp_defs['app_name']) ?> - Account</h2>
	<?php isset($error) ? "<h3>{$error}</h3>" : null ?>
	<?php if ($user->logged) : ?>
		<table width="400px" cellpadding="2" cellspacing="5">
			<tr>
				<td width="100px" align="center" valign="top">
					<img src="<?php _e($mp_defs['base_url']) ?>application/files/images/avatars/<?php _e($user->data->display_pic)?>" />
						<br />
					<!-- <input type="button" onclick="" value="Edit Account" /> -->
				</td>
				<td valign="top">
					<strong>
						<a href="<?php _e($mp_defs['base_url']) ?>user/<?php _e($user->data->display_name)?>" target="_blank"><?php _e($user->data->display_name)?></a>
					</strong>
						<br /> 
							<?php _e($user->data->first_name)?> <?php _e($user->data->last_name)?>
						<br />
						<br />
					<strong>
						API Key :
					</strong> 
						<br />
							<?php _e($user->data->account_key)?>
						<br />
						<br />
					<strong>
						Joined : 
					</strong>
						<br />
							<?php _e(date('F jS, Y', strtotime($user->data->date_created)))?>
						<br />
						<br />
					<strong>
						Registered Email :
					</strong>
						<br />
							<?php _e($user->data->email_address)?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<hr />
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<?php _e($user->data->bio)?>
				</td>
			</tr>
		</table>
	<?php else : ?>
		<form method="post">
			<h3><?php _e("Your {$mp_defs['app_name']} Account Information")?></h3>
				<table width="100%" cellpadding="2" cellspacing="5" class="editform">
					<tr>
						<td align="left" valign="top" width="25%" nowrap><?php _e("{$mp_defs['app_name']} Username: ")?></td>
						<td>
							<input type="text" name="180_display_name" id="180_display_name" size="30" value="<?php /* _e(wp_oneighty_get_option('180_display_name')) */?>" tabindex="2">
								<small>
									<br />
										<?php _e("Enter your {$mp_defs['app_name']} username.") ?><br />
										<?php _e("If you are not yet a member of {$mp_defs['app_name']}, ") ?> 
									<br />
										<a target="_blank" href="<?php _e($mp_defs['registration_url']) ?>"><?php _e('Click Here') ?></a>
										<?php _e('to create your FREE Account!') ?>
								</small>
						</td>
					</tr>
					<tr>
						<td align="left" valign="top" width="25%" nowrap><?php _e("{$mp_defs['app_name']} Password: ")?></td>
						<td>
							<input type="password" name="180_passwd" id="180_passwd" size="30" value="<?php /* _e(wp_oneighty_get_option('180_passwd')) */?>" tabindex="3">
								<small>
									<br />
										<?php _e("Enter your {$mp_defs['app_name']} password.") ?>
								</small>
						</td>
					</tr>
				</table>

					<div class="submit">
						<input type="submit" name="save_settings" value="<?php _e('Save Settings')?>">
					</div>
		</form>
	<?php endif; ?>
</div>
