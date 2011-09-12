<?php if ($user->logged) : ?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#180_syndication_info_div').dialog({
				title: 'Syndicate Article', 
				modal: true, 
				resizable: false, 
				draggable: false, 
				autoOpen: false, 
				height: 400, 
				width: 400
			});
			
			$('#180_category_id').change(function() {
				jQuery.ajax({
					type:     'post', 
					url:      '<?php echo(admin_url('admin-ajax.php')) ?>', 
					async:    false, 
					dataType: 'json', 
					data: {
						action: 'oneighty_jaxer', 
						route: 'oneighty_subcategories_grab', 
						key: '<?php echo($user->data->account_key) ?>', 
						category_id: jQuery('#180_category_id').val()
					}, 
					success: function(response) {
						var sc1_html = '<option value="">Please choose a subcategory ...</option>';
						jQuery.each(response, function(i, cat) {
							sc1_html += '<option value="' + cat.id + '">' + cat.label + '</option>';
						});
						
						jQuery('#180_subcategory_id').html(sc1_html);
						jQuery('#180_subcategory_id').attr('disabled', false);
					}
				});
			});
		});
		function syndicate_article(aid, has_comments) {
			var syndicate_comments = false;
			jQuery('#180_syndication_info_div').dialog('option', 'buttons', {
				'Syndicate': function() {
					if (has_comments) {
						var dhtml  = '<div class="misc-pub-section"><span><?php _e('Syndicate Comments') ?>:</span><br>';
							dhtml += '<input type="radio" name="180_syndicate_comments" value="1"> Yes';
							dhtml += '<input type="radio" name="180_syndicate_comments" value="0"> No';
							dhtml += '</div>';
						jQuery('#180_syndication_info_div').html(dhtml);
						jQuery('#180_syndication_info_div').dialog('option', 'title', 'What about the comments');
						jQuery('#180_syndication_info_div').dialog('option', 'buttons', {
							'Continue': function() {
								if (jQuery('#180_syndicate_comments').val() == 1) {
									syndicate_comments = true;
								}
							}
						});
					}
					
					jQuery.ajax({
						type: 'post', 
						url: '<?php echo(admin_url("admin-ajax.php")) ?>', 
						async: false, 
						dataType: 'json', 
						data: {
							action: 'oneighty_jaxer', 
							route: 'oneighty_syndicate_existing', 
							article_id: aid, 
							category_id: jQuery('#180_category_id').val(), 
							subcategory_id: jQuery('#180_subcategory_id').val(), 
							tag_word_a: jQuery('#180_tag_word_a').val(), 
							tag_word_b: jQuery('#180_tag_word_b').val(), 
							comments: syndicate_comments
						}, 
						success: function(returned) {
								if (returned.success) {
									jQuery('#180_syndication_info_div').html('<p>You have successfully syndicated your article to <?php _e($mp_defs['app_name']) ?>.</p>');
									jQuery('#180_syndication_info_div').dialog('option', 'title', 'Awesome!  You\'re done!');
									jQuery('#180_syndication_info_div').dialog('option', 'buttons', {
										'Finish': function() {
											jQuery('#180_syndication_info_div').dialog('close');
											jQuery('#180_article_record_' + aid).slideUp('slow');
										}
									});
								} else {
									jQuery('#180_syndication_info_div').html('<p>There was an error while syndicating you article to <?php _e($mp_defs['app_name']) ?>.  Please refresh the page and try again.</p>');
									jQuery('#180_syndication_info_div').dialog('option', 'title', 'Oops!  There was an error.');
									jQuery('#180_syndication_info_div').dialog('option', 'buttons', {
										'Terminate': function() {
											jQuery('#180_syndication_info_div').dialog('close');
											self.location.reload();
										}
									});
								}
						}
					});
				},
				'Cancel': function(){
					jQuery('#180_syndication_info_div').dialog('close');
				}
			});
			jQuery('#180_syndication_info_div').dialog('open');
		}
	</script>
	<table class="widefat post" width="75%" cellpadding="0">
		<thead>
			<tr>
				<th align="left" width="60%"><?php _e('Title') ?></th>
				<th align="left" width="10%"><?php _e('Comments') ?></th>
				<th align="left" width="15%"><?php _e('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if (count($articles)) : ?>
				<?php for ($a = 0; $a < count($articles); $a ++) : ?>
					<?php if ($articles[$a]->ID) : ?>
						<tr id="180_article_record_<?php echo($articles[$a]->ID) ?>">
							<td align="left"><?php _e($articles[$a]->post_title) ?></td>
							<td align="left"><?php _e(($articles[$a]->comment_count > 0) ? 'Yes' : 'No') ?></td>
							<td align="left"><input type="button" value="Syndicate to <?php _e($mp_defs['app_name']) ?>" onclick="syndicate_article(<?php echo($articles[$a]->ID) ?>, <?php echo(($articles[$a]->comment_count > 0) ? 1 : 0) ?>);"></td>
						</tr>
					<?php endif ?>
				<?php endfor; ?>
			<?php else : ?>
				<tr>
					<td colspan="3">
						You have no posts that are eligible for syndication.
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
		<tfoot></tfoot>
	</table>
	<div id="180_syndication_info_div">
		<div class="misc-pub-section">
			<span>
				<?php _e('Category') ?>:
			</span>
				<br />
			<?php wp_oneighty_category_select('180_category_id', $user->data->account_key); ?>
		</div>
			<br>
		<div class="misc-pub-section">
			<span>
				<?php _e('Sub-Category') ?>:
			</span>
				<br />
			<select name="180_subcategory_id" id="180_subcategory_id" disabled="true">
				<option value="">You must first choose a category</option>
			</select>
		</div>
			<br>
		<div class="misc-pub-section">
			<span>
				<?php _e('Tag Words: ') ?>:
			</span>
				<br />
				<input type="text" name="180_tag_word_a" id="180_tag_word_a">
				<input type="text" name="180_tag_word_b" id="180_tag_word_b">
		</div>
	</div>
<?php else : ?>
	<strong>You must <a href="<?php _e(admin_url('admin.php'))?>?page=wp_oneighty">Login</a> to view your posts to <?php _e($mp_defs['app_name']) ?>.</strong>
<?php endif; ?>
