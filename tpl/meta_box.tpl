<div id="oneighty-publish-wrap">
	<?php
		if ( ! $user->logged) :
			if (isset($error)) : 
	?>
				<div class="misc-pub-section misc-pub-section-last">
					<p>
						<b>
							<?php _e("Please verify your {$mp_defs['app_name']} account information under the <a href=\"admin.php?page=wp_oneighty\">Account tab</a>.") ?>
						</b>
					</p>
					<p>
						<?php _e($error) ?>
					</p>
				</div>
	<?php
			endif;
			return(false);
		endif; 
	?>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			// jQuery('#oneighty_syndicate_do').attr('disabled', true);

			jQuery('#mediaplace_secondcat_id').change(function(){
				jQuery.ajax({
					type: 'post',
					url: '<?php echo(admin_url("admin-ajax.php")) ?>',
					async: false,
					dataType: 'json',
					data: {
						action: 'oneighty_jaxer',
						route: 'oneighty_subcategories_grab',
						key: '<?php echo($user->data->account_key) ?>',
						category_id: jQuery('#mediaplace_secondcat_id').val()
					},
					success: function(response){
						var sc2_html = '';
						jQuery.each(response, function(i, cat){
							sc2_html += '<option value="' + cat.id + '">' + cat.label + '</option>';
						});
						
						jQuery('#mediaplace_secondsubcat_id').html(sc2_html);
						jQuery('#mediaplace_secondsubcat_id').attr('disabled', false);
					}
				});
			});
			
			jQuery('#mediaplace_group_id').change(function(){
				if (jQuery('#mediaplace_group_id').val() != 0) {
					jQuery('#mediaplace-group-privacy-section').slideDown();
				}
				else {
					jQuery('#mediaplace-group-privacy-section').slideUp();
				}
			});
		});
		
		function mediaplace_syndicate_do(){
			if (oneighty_valid()) {
				if (jQuery("input[name='mediaplace_syndicate_to']:checked").val() == 1) {
					jQuery.ajax({
						type: 'post',
						url: '<?php echo(admin_url("admin-ajax.php")) ?>',
						async: false,
						dataType: 'json',
						data: {
							action: 'oneighty_submit',
							id: jQuery('#post_ID').val(),
							title: jQuery('#title').val(),
							content: tinyMCE.get('content').getContent(),
							excerpt: jQuery('#excerpt').val(),
							mediaplace_cat_id: jQuery('#mediaplace_cat_id').val(),
							mediaplace_secondcat_id: jQuery('#mediaplace_secondcat_id').val(),
							mediaplace_group_id: 0,
							mediaplace_group_privacy: 0,
							mediaplace_cost: 0.00,
							mediaplace_allow_free: 1,
							mediaplace_tag_word_a: jQuery('#mediaplace_tag_word_a').val(),
							mediaplace_tag_word_b: jQuery('#mediaplace_tag_word_b').val(),
							mediaplace_tag_word_c: jQuery('#mediaplace_tag_word_c').val(),
							mediaplace_tag_word_d: jQuery('#mediaplace_tag_word_d').val(),
							mediaplace_syndicate_to: jQuery("input[name='mediaplace_syndicate_to']:checked").val(),
							wp_type: jQuery('#publish').val()
						},
						
						success: function(response){
							if (response.success) {
								jQuery('#oneighty_returned_message').html('<font color="#008800">Your article has been syndicated to <?php echo($mp_defs["app_name"]) ?>.');
							}
							else {
								if (jQuery("input[name='mediaplace_syndicate_to']:checked").val() == 1) {
									jQuery('#oneighty_returned_message').html('<font color="#bb0000">There was an error while syndicating your article to <?php echo($mp_defs["app_name"]) ?>.');
								}
							}
						}
					});
				}
			}
		}
		
		function oneighty_valid(){
			var is_valid = true;
			var does_need = [];
			
			if (jQuery('#title').val() == '') {
				is_valid = false;
				does_need.push('Post Title');
			}
			
			if (tinyMCE.get('content').getContent() == '') {
				is_valid = false;
				does_need.push('Post Content');
			}
			
			if (jQuery('#mediaplace_cat_id').val() == '') {
				is_valid = false;
				does_need.push('Category');
			}
			
			//all tags are optional
/*
			if (jQuery('#mediaplace_tag_word_a').val() == '') {
				is_valid = false;
				does_need.push('Tag Word 1');
			}
			
			if (jQuery('#mediaplace_tag_word_b').val() == '') {
				is_valid = false;
				does_need.push('Tag Word 2');
			}
			
*/
			if (is_valid) {
				return (true);
			}
			else {
				var err_html = '<font color="#bb0000">You need to provide the following before you can syndicate your article.<br>';
				
				jQuery.each(does_need, function(i, n){
					err_html += does_need[i] + '<br>';
				});
				
				err_html += '</font>';
				
				jQuery('#oneighty_returned_message').html(err_html);
			}
		}
	</script>
	<div class="misc-pub-section">
		<span>
			<?php _e('Category') ?>:
		</span>
			<br />
		<?php wp_oneighty_category_select('mediaplace_cat_id', $user->data->account_key); ?>
	</div>
	
<!--
	<div class="misc-pub-section">
		<span>
			<?php _e('Sub-Category') ?>:
		</span>
			<br />
		<select name="mediaplace_subcat_id" id="mediaplace_subcat_id" disabled="true">
			<option value="">You must first choose a category</option>
		</select>
	</div>
-->
	
	<div class="misc-pub-section">
		<span>
			<?php _e('Category 2') ?>:
		</span>
			<br />
		<?php wp_oneighty_category_select('mediaplace_secondcat_id', $user->data->account_key); ?>
	</div>
	
<!--
	<div class="misc-pub-section">
		<span>
			<?php _e('Sub-Category 2') ?>:
		</span>
			<br />
		<select name="mediaplace_secondsubcat_id" id="mediaplace_secondsubcat_id" disabled="true"></select>
	</div>
-->
	
	<div class="misc-pub-section">
		<span>
			<?php _e('Group') ?>:
		</span>
			<br />
		<?php wp_oneighty_group_select('mediaplace_group_id', $user->data->account_key, $user->data->id); ?>
	</div>
	
	<div class="misc-pub-section" id="mediaplace-group-privacy-section" style="display:none">
		<span>
			<?php _e('Available to Group Members Only') ?>:
		</span>
			<br />
		<input type="radio" id="group_privacy" name="mediaplace_group_privacy" value="0" checked="checked"> No
		<input type="radio" id="group_privacy" name="mediaplace_group_privacy" value="1"> Yes
	</div>
	
	<div class="misc-pub-section">
		<span>
			<?php _e('Cost') ?>:
		</span>
			<br />
        <?php wp_oneighty_cost_select('mediaplace_cost') ?>
	</div>
	
	<div class="misc-pub-section">
		<span>
			<?php _e('Tag Words: ') ?>:
		</span>
			<br />
			<input type="text" name="mediaplace_tag_word_a" id="mediaplace_tag_word_a">
			<input type="text" name="mediaplace_tag_word_b" id="mediaplace_tag_word_b">
			<input type="text" name="mediaplace_tag_word_c" id="mediaplace_tag_word_c">
			<input type="text" name="mediaplace_tag_word_d" id="mediaplace_tag_word_d">
	</div>
	
	<div class="misc-pub-section">
		<span>
			<?php _e("Syndicate to {$mp_defs['app_name']}") ?>:
		</span>
			<br />
			<input type="radio" name="mediaplace_syndicate_to" id="mediaplace_syndicate_to" value="0" checked="checked">No
			<input type="radio" name="mediaplace_syndicate_to" id="mediaplace_syndicate_to" value="1"> Yes
	</div>
    
	<div class="misc-pub-section-last">
		<span id="oneighty_returned_message"></span>
			<br>
		<input type="button" id="oneighty_syndicate_do" class="button-primary" value="Syndicate to <?php echo($mp_defs['app_name']) ?>" onclick="mediaplace_syndicate_do()">
	</div>
</div>
