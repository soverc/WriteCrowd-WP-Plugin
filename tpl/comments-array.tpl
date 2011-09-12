<?php 
	$comments = array();

	if (count($cmnts['dec'])) {
		foreach ($cmnts['dec'] as $c) {
			$comment = new stdClass();
				$comment->comment_ID           = $c->id;
				$comment->comment_post_ID      = $c->article_id;
				$comment->comment_author       = $c->author_name;
				$comment->comment_author_email = $c->author_email;
				$comment->comment_author_url   = stripslashes($mp_defs['base_url']).$c->author_name;
				$comment->comment_author_IP    = $c->author_ip;
				$comment->comment_date         = $c->date_created;
				$comment->formatted_date       = date('F j, Y', strtotime($c->date_created));
				$comment->formatted_time       = date('g:i a', strtotime($c->date_created));
				$comment->comment_date_gmt     = gmdate('Y-m-d H:i:s', strtotime($c->date_created));
				$comment->comment_content      = $c->content;
				$comment->comment_karma        = 0;
				$comment->comment_approved     = 1;
				$comment->comment_agent        = null;
				$comment->comment_type         = null;
				$comment->comment_parent       = $c->parent_id | 0;
				$comment->user_id              = $c->author_id | 0;
				$comment->from_blog            = ((null == $c->from_blog) ? 'Unknown Blog' : $c->from_blog);
				$comment->from_url             = ((null == $c->from_url) ? '#' : $c->from_url);
			$comments[] = $comment;
		}
	}
?>
