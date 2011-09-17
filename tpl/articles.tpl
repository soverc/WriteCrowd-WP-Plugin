<div class="wrap">
	<script type="text/javascript">
		function disableDialogButton( dialog_selector, button_name )
		{
			var buttons = jQuery( dialog_selector + ' .ui-dialog-buttonpane button' );
			for ( var i = 0; i < buttons.length; ++i )
			{
				var jButton = jQuery( buttons[i] );
				if ( jButton.text() == button_name )
				{
					jButton.attr('disabled', 'disabled' ).addClass( 'disabled' );
					return jButton;
				}
			}

			return null;
		}

		jQuery(document).ready(function() {
			jQuery('#remove_article').dialog({
				dialogClass: 'removeDialog',
				title: 'Remove Article', 
				autoOpen: false,
				height: 200, 
				width: 400, 
				draggable: false,
				resizable: false
			});
		});

		function removeArticle(post_id) {
			jQuery('#remove_article').dialog('option', 'buttons', {
				'Yes': function() {
					disableDialogButton('.removeDialog', 'Yes');
					jQuery.ajax({
						type: 'post',
						url: '<?php echo(admin_url("admin-ajax.php")) ?>', 
						dataType: 'json', 
						data: {
							action: 'oneighty_jaxer', 
							route: 'remove_article_from_site',
							id: post_id
						}, 
						success: function(response) {
							if (response.success) {
							jQuery('#remove_article').html('The article has been removed from your site.');
							jQuery('#remove_article').dialog('option', 'buttons', {
								'Continue': function() {
									jQuery('#remove_article').dialog('close');
									jQuery('#article_info_' + post_id).slideUp();
								}
							});
							} else {
							jQuery('#remove_article').html('Failed to remove article. :(');
							}
						}
					});
				},

				'No': function() {
					jQuery('#remove_article').dialog('close');
				}
			});

			jQuery('#remove_article').html('Are you sure you wish to remove this article from your blog?');
			jQuery('#remove_article').dialog('open');
			return false;
		}
	</script>
	<h2><?php _e($mp_defs['app_name']) ?> - Syndicated Articles</h2>
	<?php isset($error) ? "<h3>{$error}</h3>" : null?>
	<p><?php _e("This page displays blog posts that you syndicated to and from {$mp_defs['app_name']}.") ?></p>
		<?php if ($user->logged) : ?>
			<table class="widefat post fixed" cellpadding="0">
				<thead>
					<tr>
						<th><?php _e('Post') ?></th>
						<th><?php _e('Category') ?></th>
						<th><?php _e('Created By') ?></th>
						<th><?php _e('Submitted') ?></th>
						<th><?php _e('Actions') ?></th>
					</tr>
				</thead>
				<tbody>
				
				<?php if (count($articles) == 0) : ?>
					<tr>
						<td colspan="4">
							<?php _e("Sorry, you do not have any blog posts submitted to {$mp_defs['app_name']}.") ?>
						</td>
					</tr>
				<?php else : ?>
					<?php foreach($articles as $a): ?>
						<tr id="article_info_<?php _e($a->post_id) ?>">
							<td class="post-title">
								<strong>
									<a class="row-title" href="<?php _e(get_bloginfo('url')) ?>/?p=<?php _e($a->post_id) ?>"><?php _e($a->data->title) ?></a>
								</strong>
							</td>
							<td>
								<?php _e($a->data->cat_label) ?> | <?php _e($a->data->secondcat_label) ?>
							</td>
							<td>
								<?php _e(($a->data->author_id == $user->id) ? 'Me' : "{$mp_defs['app_name']} Author") ?>
							</td>
							<td>
								<?php _e(date('g:i a', strtotime($a->data->date_created))) ?> on <?php _e(date('F jS, Y', strtotime($a->data->date_created)))?>
							</td>
							<td>
								<a href="<?php _e(get_bloginfo('url')) ?>/?p=<?php _e($a->post_id) ?>">View</a> | 
								<a href="#" onclick="return removeArticle('<?php _e($a->post_id) ?>');">Remove</a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				
				</tbody>
			</table>
		<?php else : ?>
			<strong>You must <a href="<?php _e(admin_url('admin.php?page=wp_oneighty')) ?>">Login</a> to view your posts to <?php _e($mp_defs['app_name']) ?>.</strong>
		<?php endif; ?>
<div id="remove_article">Are you sure you wish to remove this article from your blog?</div>
</div>
