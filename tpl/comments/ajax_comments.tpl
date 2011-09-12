<li [%COMMENT_CLASS%] id="li-comment-[%COMMENT_ID%]">
	<!-- Begin Comment -->
	<div id="comment-[%COMMENT_ID%]">
		<div class="comment-author vcard">
				<cite class="fn">
					<a href="[%COMMENT_AUTHOR_URL%]">[%COMMENT_AUTHOR%]</a> From <a href="[%COMMENT_FROM_URL%]">[%COMMENT_FROM_BLOG%]</a>
				</cite>
				<span class="says">says:</span>
		</div>

		<div class="comment-meta commentmetadata">
			<!-- Permalink <a href="<?php echo(esc_url(get_comment_link($comment->comment_ID)))?>"><?php echo(esc_url(get_comment_link($comment->comment_ID)))?></a> -->
				[%COMMENT_DATE%] at 
				[%COMMENT_TIME%]
		</div><!-- .comment-meta .commentmetadata -->
		
		<!-- Comment Body -->
		<div class="comment-body">[%COMMENT_CONTENT%]</div>
		
		<!-- Comment Reply -->
		<div class="reply"></div>
	</div><!-- End Comment -->
</li>
