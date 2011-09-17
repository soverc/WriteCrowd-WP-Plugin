<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#article-preview').dialog({
			draggable: false,
			resizable: false,
			modal: true,
			autoOpen: false,
			width: 800,
			height: 600,
			buttons: {
				'Close': function() {
					jQuery(this).dialog('close');
				}
			}
		});
		
		jQuery('#syndicate_comments_with_article').dialog({
			title: 'One more thing ...',
			draggable: false,
			resizable: false,
			modal: true,
			autoOpen: false,
			width: 400,
			height: 250
		});
		
		
		
		jQuery.display_article = function(id, title) {
			var mp_article = jQuery('#article-content-' + id).html();
			
			jQuery('#article-preview').html(mp_article);
			jQuery('#article-preview').dialog('option', 'title', title);
			jQuery('#article-preview').dialog('open');
		}
		
		jQuery.syndicate_comments = function(id, article_title) {
			jQuery('#syndicate_comments_with_article').dialog('option', 'buttons', {
				'Syndicate': function() {
					var data = {
						article_id: id, 
						title: article_title, 
						syndicate_comments: jQuery('#syndicate_comments_answer').val()
					};
					
					jQuery.syndicate_article(data);
				}
			});
			
			jQuery('#syndicate_comments_with_article').dialog('open');
		}
		
		jQuery.syndicate_article = function(data) {
			data.action  = 'oneighty_syndicate_to_site';
			
			jQuery.ajax({
				type: 'post',
				url: '<?php echo(admin_url('admin-ajax.php')); ?>',
				async: true,
				data: data,
				dataType: 'json',
				complete: function(dataResponse){
					jQuery('#syndicate_comments_with_article').html('<em>"' + data.title + '"</em> has been successfully syndicated to your website!');
					jQuery('#syndicate_comments_with_article').dialog('option', 'buttons', {
						'Finish': function() {
							jQuery('#syndicate_comments_with_article').dialog('close');
							jQuery('#article-result-' + data.article_id).slideUp('slow');
						}
					});	
				}
			});
		}											
	});
</script>
<div class="wrap">
	<h2><?php _e($mp_defs['app_name']) ?> - Article Search</h2>
	<?php isset($error) ? "<h3>{$error}</h3>" : null?>
	<p><?php _e("This page allows you to search the {$mp_defs['app_name']} Syndicate Database.") ?></p>
	<div id="article-preview"></div>
		<?php if ($user->logged) : ?>
			<form method="post" id="180_search_form">
				<table>
					<tr>
						<td id="180_search_criteria_container">
							<input type="text" name="180_search_criteria" id="180_search_criteria" size="50" <?php _e(isset($_POST['180_search']) ? 'value="'.$_POST['180_search_criteria'].'"' : null) ?> />
							<input type="submit" name="180_search" id="180_search" value="Search <?php _e($mp_defs['app_name']) ?>" />
						</td>
					</tr>
				</table>
			</form>
				<br />
				<br />
				<?php if(isset($articles['dec'])) : ?>
					<table class="widefat post fixed" cellpadding="0">
						<thead>
							<tr>
								<th width="40%"><?php _e('Article Name') ?></th>
                                <th width="15%"><?php _e('Syndications') ?></th>
                                <th width="10%"><?php _e('Comments') ?></th>
                                <th width="15%"><?php _e('Date Created') ?></th>
                                <th width="10%"><?php _e('Cost') ?></th>
                                <th width="10%"><?php _e('Actions') ?></th>
							</tr>
						</thead>
						<tbody>
						<?php if (!count($articles['dec'])) : ?>
							<tr>
								<td colspan="5">
									<?php _e('Sorry, no articles were founding matching that criteria') ?>
								</td>
							</tr>
						<?php else : ?>
							<?php foreach($articles['dec'] as $a): ?>
									<tr id="article-result-<?php _e($a->id) ?>">
										<td class="post-title">
											<a href="#" onclick="jQuery.display_article('<?php _e($a->id) ?>', '<?php _e($a->title) ?>');"><?php _e(stripslashes($a->title)) ?></a>
											<div id="article-content-<?php _e($a->id) ?>" style="display:none;">
												<?php echo($a->content); ?>
											</div>
										</td>
										<td><?php _e($a->syndications) ?></td>
										<td><?php _e($a->comments) ?></td>
										<td><?php _e(date('M jS, Y', strtotime($a->date_created))) ?></td>
										<td>
											<?php if (0.00 == $a->cost) : ?>
												Free
											<?php else : ?>
												$<?php _e($a->cost) ?>
											<?php endif ?>
										</td>
										<td>
											<?php if ( ! in_array($a->id, $syndicated_posts)) : ?>
												<input type="button" onclick="jQuery.syndicate_comments('<?php _e($a->id) ?>', '<?php _e(addslashes($a->title))?>');" value="Syndicate">
											<?php else : ?>
												<input type="button" disabled="DISABLED" class="disabled" value="Syndicated">
												<!-- <a href="#">$<?php _e($a->cost) ?> without Ads</a> -->
											<?php endif; ?>
										</td>
									</tr>
							<?php endforeach; ?>
						<?php endif; ?>
						
						</tbody>
						<tfoot></tfoot>
					</table>
				<?php endif; ?>
				<div id="syndicate_comments_with_article">
					Would you also like to syndicate the comments associated with this article?
						<br />
						<br />
					<center>
						<select id="syndicate_comments_answer">
							<option value="1" selected="selected">Yes</option>
							<option value="0">No</option>
						</select>
					</center>
				</div>
		<?php else : ?>
			<strong>You must <a href="<?php _e(admin_url('admin.php?page=wp_oneighty')) ?>">Login</a> to search the <?php _e($mp_defs['app_name']) ?> Syndicate.</strong>
		<?php endif; ?>
</div>
