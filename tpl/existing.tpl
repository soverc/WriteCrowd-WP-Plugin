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
							secondcategory_id: jQuery('#180_secondcategory_id').val(), 
							tag_word_a: jQuery('#180_tag_word_a').val(), 
							tag_word_b: jQuery('#180_tag_word_b').val(), 
							tag_word_c: jQuery('#180_tag_word_c').val(), 
							tag_word_d: jQuery('#180_tag_word_d').val(), 
							comments: syndicate_comments
						}, 
						success: function(returned) {
								if (returned.success) {
									jQuery('#180_syndication_info_div').html('<p>You have successfully syndicated your article to <?php _e($mp_defs['app_name']) ?>.</p>');
									jQuery('#180_syndication_info_div').dialog('option', 'title', 'Awesome!  You\'re done!');
									jQuery('#180_syndication_info_div').dialog('option', 'buttons', {
										'Finish': function() {
											jQuery('#180_syndication_info_div').dialog('close');
											jQuery('#180_article_sbtn_' + aid).attr('value', 'Syndicated');
											jQuery('#180_article_sbtn_' + aid).attr('disabled', 'DISABLE');
											jQuery('#180_article_sbtn_' + aid).attr('class', 'disabled');
//											location.reload(true);
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
				<?php foreach ($articles as $_key => $_article) : ?>
					<?php if ($_article->ID) : ?>
						<tr id="180_article_record_<?php echo($_article->ID) ?>">
							<td align="left"><?php _e($_article->post_title) ?></td>
							<td align="left"><?php _e(($_article->comment_count > 0) ? 'Yes' : 'No') ?></td>
							<?php if ($_article->post_id): ?>
								<td align="left"><input type="button" class="disabled" id="180_article_sbtn_<?php echo($_article->ID);?>" value="Syndicated" disabled="DISABLE"/></td>
							<?php else: ?>
								<td align="left"><input type="button" id="180_article_sbtn_<?php echo($_article->ID);?>"  value="Syndicate to <?php _e($mp_defs['app_name']) ?>" onclick="syndicate_article(<?php echo($_article->ID) ?>, <?php echo(($_article->comment_count > 0) ? 1 : 0) ?>);"></td>
							<?php endif ?>
						</tr>
					<?php endif ?>
				<?php endforeach; ?>
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
				<?php _e('Category 2') ?>:
			</span>
				<br />
			<?php wp_oneighty_category_select('180_secondcategory_id', $user->data->account_key); ?>
		</div>
			<br>
<!--
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
-->
		<div class="misc-pub-section">
			<span>
				<?php _e('Tag Words: ') ?>:
			</span>
				<br />
				<input type="text" name="180_tag_word_a" id="180_tag_word_a">
				<input type="text" name="180_tag_word_b" id="180_tag_word_b">
				<input type="text" name="180_tag_word_c" id="180_tag_word_c">
				<input type="text" name="180_tag_word_d" id="180_tag_word_d">
		</div>
	</div>
<?php else : ?>
	<strong>You must <a href="<?php _e(admin_url('admin.php'))?>?page=wp_oneighty">Login</a> to view your posts to <?php _e($mp_defs['app_name']) ?>.</strong>
<?php endif; ?>
