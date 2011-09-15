<?php session_start();
/*
Plugin Name: 180Create Syndication
Plugin URI: https://www.180create.com/
Description: The 180Create Syndication Plugin allows you to submit your own articles to the 180Create Syndicate as well as pull content to your WordPress site from the 180Create Syndicate.
Version: 0.98b-demo
Author: 180Create.com
Author URI: https://www.180Create.com/
*/

// Global MediaPlace Definitions
$mp_defs = array(
	'plugin_name'      => '180Create Content Syndication', 
	'app_name'         => '180Create', 
	'plugin_version'   => '0.98b-demo', 
	'min_php_version'  => '5.0', 
	'min_wp_version'   => '2.6', 
	'plugin_path'      => WP_CONTENT_URL.'/plugins/oneighty', 
	'plugin_root'      => ABSPATH.'wp-content/plugins/oneighty', 
	'template_path'    => ABSPATH.'wp-content/plugins/oneighty/tpl', 
	'class_path'       => ABSPATH.'wp-content/plugins/oneighty/classes', 
	'assets_path'      => ABSPATH.'wp-content/plugins/oneighty/assets', 
	'images_path'      => WP_CONTENT_URL.'/plugins/oneighty/images', 
	'base_url'         => 'https://www.writecrowd.com/', 
	'xmlrpc_url'       => 'https://www.writecrowd.com/feeds/xmlrpc.php', 
	'jsonrpc_url'      => 'https://www.writecrowd.com/feeds/jsonrpc.php', 
	'registration_url' => 'https://www.writecrowd.com/signup.pl'
);

if (is_admin()) {
	register_activation_hook(__FILE__, 'wp_oneighty_install');
	register_deactivation_hook(__FILE__, 'wp_oneighty_deactivate');

	// Create the sidebar menu for 180 Create
	add_action('admin_menu', 'wp_oneighty_add_menu');
	
	// Add the meta box to the Post form so we can collect our "special data"
	add_action('admin_menu', 'wp_oneighty_add_post_meta_box');
	
	// Our Post Submission Ajaxer Server Function
	add_action('wp_ajax_oneighty_submit', 'wp_oneighty_submit');
	
	// This is the function that is run whenever someone is making a new post.  It grabs all of the categories.
	add_action('wp_ajax_oneighty_syndicate_to_site', 'wp_oneighty_syndicate_to_site');
	
	// Comment Post Ajaxer
	add_action('wp_ajax_oneighty_jaxer', 'wp_oneighty_jaxer');
	
	// Load CSS and Javascript
	add_action('admin_head', 'wp_oneighty_css');
	
	// WordPress Init
	add_action('init', 'wp_oneighty_assets');
}
	// Load CSS and Javascript
	add_action('wp_head', 'wp_oneighty_noadmin_assets', 0);

	// Comment Post Ajaxer
	add_action('wp_ajax_nopriv_oneighty_jaxer', 'wp_oneighty_jaxer');

	// Display Post Title
	add_filter('the_title', 'wp_oneighty_post_title');

	// Display Post Content
	add_filter('the_content', 'wp_oneighty_post_content');

	// Display Post Author
	add_filter('the_author', 'wp_oneighty_post_author');
	
	// Post Author Link
	add_filter('author_link', 'wp_oneighty_post_author_link');
	
	// Post Date
	add_filter('get_the_date', 'wp_oneighty_post_date');
	
	// Run when displaying Comments for a single post
	add_filter('comments_array', 'wp_oneighty_comment_handler');
	
	// Use Our Comments Template
	add_filter('comments_template', 'wp_oneighty_comment_template_handler');
	
function wp_oneighty_is_article($id)
{
	global $wpdb, $mp_defs;
	
	$article = $wpdb->get_results("SELECT post_id FROM {$wpdb->prefix}mediaplace_posts WHERE post_id = '".strip_tags($id)."'");
	
	if (count($article)) {
		return(true);
	} else {
		return(false);
	}
}

function wp_oneighty_article_info($id)
{
	global $wpdb, $mp_defs;
	
	$article = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mediaplace_posts WHERE post_id = '".strip_tags($id)."'");
	
	if (count($article)) {
		$article       = $article[0];
		$article->data = json_decode($article->article_data);
	}
return($article);
}

function wp_oneighty_user_details($id = null)
{
	global $wpdb, $mp_defs;
	
	$user   = $wpdb->get_results("SELECT user_data FROM {$wpdb->prefix}mediaplace_user");
	$return = new stdClass();
		
	if (count($user)) {
		$user         = $user[0];
		$user         = json_decode($user->user_data);
		
		$response = __json_call(array(
			'_method' => 'fetch_user', 
			'_key'    => $user->account_key, 
			'user_id' => ((null == $id) ? $user->id : $id)
		));
		
		if ($response['dec']->error) {
			$response->logged = false;
		} else {
			$return->logged   = true;
			$return->data     = $response['dec'];
			$return->raw      = $response['enc'];
		}
		
		return($return);
	} else {
		return(false);
	}
}

function wp_oneighty_clean_content($string)
{
	$string = (string) utf8_encode($string);
		return($string);
}

function wp_oneighty_assets()
{
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('jquery-ui-draggable');
	wp_enqueue_script('jquery-ui-droppable');
	wp_enqueue_script('jquery-ui-resizable');
	wp_enqueue_script('jquery-ui-selectable');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('jquery-ui-tabs');
}

function wp_oneighty_noadmin_assets()
{
	global $mp_defs;
	
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('jquery-ui-draggable');
	wp_enqueue_script('jquery-ui-droppable');
	wp_enqueue_script('jquery-ui-resizable');
	wp_enqueue_script('jquery-ui-selectable');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('jquery-ui-tabs');
		echo("<link href=\"{$mp_defs['plugin_path']}/assets/css/ui.jquery.css\" rel=\"stylesheet\">\n");
	
	if (get_the_ID()) {
		if (wp_oneighty_is_article(get_the_ID())) {
			$post = wp_oneighty_article_info(get_the_ID());
				echo("<meta name=\"syndication-source\" content=\"{$post->data->from_url}\">\n");
		}
	}
}

function wp_oneighty_css()
{
	global $mp_defs;
	require_once($mp_defs['template_path'].'/assets-css.tpl');
}

function wp_oneighty_install()
{
	global $wpdb, $mp_defs;
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	
	# MediaPlace Posts Store
	$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mediaplace_posts` (`id` INT(255) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `post_id` INT(255) UNSIGNED DEFAULT NULL, `mediaplace_id` INT(255) UNSIGNED DEFAULT NULL, `article_data` TEXT DEFAULT NULL) ENGINE=MYISAM AUTO_INCREMENT=1;";
	
		# Commit SQL
		dbDelta($sql);
	
	# MediaPlace Comments Store
	$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mediaplace_comments` (`id` INT(255) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `comment_id` INT(255) UNSIGNED DEFAULT NULL, `mediaplace_id` INT(255) UNSIGNED DEFAULT NULL, `comment_data` TEXT DEFAULT NULL) ENGINE=MYISAM AUTO_INCREMENT=1;";
	
		# Commit SQL
		dbDelta($sql);
	
	# MediaPlace User Store
	$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mediaplace_user` (`id` INT(255) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `user_id` INT(255) UNSIGNED DEFAULT NULL, `user_data` TEXT DEFAULT NULL, `group_data` TEXT DEFAULT NULL) ENGINE=MYISAM AUTO_INCREMENT=1;";

		# Commit SQL
		dbDelta($sql);
		
	# MediaPlace User Store
	$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mediaplace_log` (`id` INT(255) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `raw` TEXT NULL DEFAULT NULL, `created` TIMESTAMP NULL DEFAULT NULL) ENGINE=MYISAM AUTO_INCREMENT=1;";

		# Commit SQL
		dbDelta($sql);
}

function wp_oneighty_deactivate()
{
	global $wpdb, $mp_defs;
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	
	# Drop MediaPlace Posts Store
	$sql = "DROP TABLE {$wpdb->prefix}mediaplace_posts";
	
		# Commit SQL
		dbDelta($sql);
		
	# Drop MediaPlace Comments Store
	$sql = "DROP TABLE {$wpdb->prefix}mediaplace_comments";
	
		# Commit SQL
		dbDelta($sql);
	
	# Drop MediaPlace User Store
	$sql = "DROP TABLE {$wpdb->prefix}mediaplace_user";
	
		# Commit SQL
		dbDelta($sql);
		
	# Drop MediaPlace Posts
	$wpdb->query("DELETE FROM {$wpdb->prefix}posts WHERE post_content LIKE 'mediaplace_article:%'");
}

function wp_oneighty_add_menu()
{
	global $mp_defs;
	add_menu_page($mp_defs['app_name'], $mp_defs['app_name'], 6, 'wp_oneighty', 'wp_oneighty', $mp_defs['images_path'].'/180logo.png');
		add_submenu_page('wp_oneighty', __('', $mp_defs['plugin_name']), __('Account',          $mp_defs['plugin_name']), 6, 'wp_oneighty',          'wp_oneighty');
		add_submenu_page('wp_oneighty', __('', $mp_defs['plugin_name']), __('Articles',         $mp_defs['plugin_name']), 6, 'wp_oneighty_articles', 'wp_oneighty_articles');
		add_submenu_page('wp_oneighty', __('', $mp_defs['plugin_name']), __('Search',           $mp_defs['plugin_name']), 6, 'wp_oneighty_search',   'wp_oneighty_search');
		add_submenu_page('wp_oneighty', __('', $mp_defs['plugin_name']), __('Existing Content', $mp_defs['plugin_name']), 6, 'wp_oneighty_existing', 'wp_oneighty_existing');
		add_submenu_page('wp_oneighty', __('', $mp_defs['plugin_name']), __('Logout',           $mp_defs['plugin_name']), 6, 'wp_oneighty_logout',   'wp_oneighty_logout');
}

function wp_oneighty_add_post_meta_box()
{
	global $mp_defs;
	if (function_exists('add_meta_box')) {
		add_meta_box( 'wp-oneighty', $mp_defs['plugin_name'], 'wp_oneighty_post_meta_box', 'post', 'side', 'high');
	}
}

function wp_oneighty_post_meta_box()
{
	global $post, $wpdb, $mp_defs;

	
	if (wp_oneighty_user_details()) {
		$user = wp_oneighty_user_details();
			require_once($mp_defs['template_path'].'/meta_box.tpl');
	}
}

function wp_oneighty()
{
	global $current_user, $wpdb, $mp_defs; 
	
	if (isset($_POST['save_settings'])) {
		$user = wp_oneighty_login($_POST);
		
		if ($user) {
			$user   = wp_oneighty_user_details();	
		}
		
		elseif (isset($user['error'])) {
			$error = $user['error'];
		}
	}
	
	elseif (wp_oneighty_user_details()) {
		$user = wp_oneighty_user_details();
	}
	
	require_once($mp_defs['template_path'].'/oneighty.tpl');
}

function wp_oneighty_login($details)
{
	global $wpdb, $mp_defs;
	
	$data = array(
		'_method'  => 'logon', 
		'username' => $details['180_display_name'], 
		'passwd'   => $details['180_passwd']
	);
	
	$user = __json_call($data);

	if ($user['dec']->id) {
		if ($wpdb->query("INSERT INTO {$wpdb->prefix}mediaplace_user (user_id, user_data) VALUES ('{$user['dec']->id}', '".addslashes($user['enc'])."')")) {
			return(true);
		} else {
			return(false);
		}
	} else {
		return($user['dec']->error);
	}
}

function wp_oneighty_logout()
{
	global $wpdb, $mp_defs;
	
	if (isset($_POST['180_logout_do'])) {
		if ($wpdb->query("DELETE FROM {$wpdb->prefix}mediaplace_user")) {
			echo('<script type="text/javascript">self.location=\''.admin_url('admin.php?page=wp_oneighty').'\';</script>');
		}
	} else {
		require_once("{$mp_defs['template_path']}/logout.tpl");
	}
}

function wp_oneighty_articles()
{
	global $wpdb, $mp_defs;
	$user = wp_oneighty_user_details();
	
	if ($user->logged) {
		$articles       = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mediaplace_posts");
			
		foreach ($articles as $article) {
			$article->data = json_decode($article->article_data);
		}
	}
	
	require_once($mp_defs['template_path'].'/articles.tpl');
}

function wp_oneighty_search()
{
	global $wpdb, $mp_defs;
	$user             = wp_oneighty_user_details();
	$aids             = $wpdb->get_Results("SELECT mediaplace_id FROM {$wpdb->prefix}mediaplace_posts");
	$syndicated_posts = array();
	
	foreach ($aids as $id) {
		$syndicated_posts[] = $id->mediaplace_id;
	}
	
	if ($user->logged) {
		if (isset($_POST['180_search'])) {
			$articles = wp_oneighty_search_run($user->data->account_key, $_POST);
			
			if (isset($articles['dec']->error)) {
				$error = $articles['dec']->error;
			}
		}
	}
	
	require_once($mp_defs['template_path'].'/search.tpl');
}

function wp_oneighty_search_run($key, $details)
{
	global $mp_defs;
	$data     = array(
		'_method'  => 'search', 
		'_key'     => $key, 
		'criteria' => $details['180_search_criteria']
	);
	$articles = __json_call($data);
		return($articles);
}

function wp_oneighty_existing()
{
	global $wpdb, $mp_defs;
	require_once($mp_defs['class_path'].'/xmlrpc/lib/xmlrpc.inc');

	$user     = wp_oneighty_user_details();
	//$articles = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type = 'post' AND post_title != 'Hello world!'");
	//do we really need to expluded hello world?
//	var_dump("SELECT * FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type = 'post'");
	//$articles = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type = 'post'");
	$articles = $wpdb->get_results("SELECT A.*, B.post_id FROM {$wpdb->prefix}posts AS A
		LEFT JOIN {$wpdb->prefix}mediaplace_posts AS B ON A.id = B.post_id
		WHERE A.post_status = 'publish' AND A.post_type = 'post'
		");

	/*
	if (count($articles)) {
		//this messes with the array keys, need foreach access to $articles 
		// from here on out.
		for ($i = 0; $i < count($articles); $i ++) {
			if (wp_oneighty_is_article($articles[$i]->ID)) {
				unset($articles[$i]);
			}
		}
	}
	 */

	require_once($mp_defs['template_path'].'/existing.tpl');
}

function wp_oneighty_cost_select($identifier)
{
	global $mp_defs;
	
	$increments = array(0.00, 1.00, 2.50, 5.00, 7.50, 10.00, 12.50, 15.00, 17.50, 20, 25, 30, 35, 40, 45, 50);
	$html  = '<select name="'.$identifier.'" id="'.$identifier.'">';

	foreach ($increments as $price) {
		if ($price == 0.00) {
			$html .= '<option value="'.number_format($price, 2).'">Free</option>';
		} else {
			$html .= '<option value="'.number_format($price, 2).'">$'.number_format($price, 2).'</option>';
		}
	}

	$html .= '</select>';
return(print($html));
}

function wp_oneighty_category_select($identifier, $key, $extra = null)
{
	global $mp_defs;
			
	$data = array(
		'_method' => 'all_categories', 
		'_key'    => $key
	);
	
	$categories = __json_call($data);
	
	$html    = '<select name="'.$identifier.'" id="'.$identifier.'"';
	
	if (null != $extra) {
		foreach ($extra as $k => $v) {
			$html .= ' '.$k.'="'.$v.'"';
		}
	}
	
	$html .= '><option value="">Please choose a category ...</option>';
	
	foreach ($categories['dec'] as $cat) {
		$html .= '<option value="'.$cat->id.'">'.$cat->label.'</option>';
	}
	
	$html   .= '</select>';
		return(print($html));
}

function wp_oneighty_subcategory_select()
{
	global $mp_defs;
	
	$data = array(
		'_method'     => 'subcategories', 
		'_key'        => strip_tags($_POST['key']), 
		'category_id' => strip_tags($_POST['category_id'])
	);
	$categories = __json_call($data);
	return($categories['enc']);
}

function wp_oneighty_group_select($identifier, $key, $id)
{	
	global $mp_defs;

	$data    = array(
		'_method' => 'groups', 
		'_key'    => $key, 
		'user_id' => $id
	);
	
	$groups = __json_call($data);
	
	$html    = '<select name="'.$identifier.'" id="'.$identifier.'"><option value="0">No Group</option>';
		
	foreach ($groups['dec']->all as $group) {
		$html .= '<option value="'.$group->id.'">'.$group->name.'</option>';
	}
	$html   .= '</select>';
		return(print($html));
}

// This handles the submit logic
function wp_oneighty_submit()
{	
	global $wpdb, $mp_defs;

	if ($_POST['wp_type'] == 'Publish' || $_POST['wp_type'] == 'Update') {
		if ($_POST['mediaplace_syndicate_to']) {
			$post_id = strip_tags($_POST['id']);
			$user    = wp_oneighty_user_details();
			if(null != $post_id) {
				$syndicated = $wpdb->get_results("SELECT mediaplace_id FROM {$wpdb->prefix}mediaplace_posts WHERE post_id = '{$post_id}'");
				
				if (count($syndicated)) {
					$ajax = array(
						'success' => false,
						'message' => "Article already syndicated."
					);

					echo(json_encode($ajax));
					exit();
				}
				$_taga = strip_tags(preg_replace('/[^A-Za-z0-9_\-]/', '', $_POST['mediaplace_tag_word_a']));
				$_tagb = strip_tags(preg_replace('/[^A-Za-z0-9_\-]/', '', $_POST['mediaplace_tag_word_b']));
				$_tagc = strip_tags(preg_replace('/[^A-Za-z0-9_\-]/', '', $_POST['mediaplace_tag_word_c']));
				$_tagd = strip_tags(preg_replace('/[^A-Za-z0-9_\-]/', '', $_POST['mediaplace_tag_word_d']));
				
				$details = array(
					'_method'              => 'post', 
					'_key'                 => $user->data->account_key, 
					'author_id'            => $user->data->id,
					'content'              => $_POST['content'],
					'title'                => strip_tags($_POST['title']),
					'description'          => strip_tags($_POST['excerpt']),
					'category_id'          => strip_tags($_POST['mediaplace_cat_id']),
					'secondcategory_id'    => strip_tags($_POST['mediaplace_secondcat_id']), 
					'group_id'             => strip_tags($_POST['mediaplace_group_id']), 
					'private'              => ((null == $_POST['mediaplace_group_privacy']) ? 0 : strip_tags($_POST['mediaplace_group_privacy'])), 
					'tag_words'            => "{$taga},{$tagb},{$tagc},{$tagd}",
					'cost'                 => strip_tags($_POST['mediaplace_cost']), 
					'allow_free'           => strip_tags($_POST['mediaplace_allow_free']),
					'name'                 => preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($_POST['title']))),
					'from_blog'            => get_bloginfo('name'), 
					'from_url'             => get_bloginfo('url')
				);
				
				$now     = date('Y-m-d H:i:s');
				$article = __json_call($details);

				//if $article['dec'] is null, there was a json_decode() error
				if ( $article['dec'] == NULL ) {
					$ajax = array(
						'success' => false,
						'message' => "Communications error"
					);

				} else if (isset($article['dec']->error)) {
					$ajax = array(
						'success' => false,
						'message' => $article['dec']->error
					);

				} else {
					$sql     = "INSERT INTO {$wpdb->prefix}mediaplace_posts (
						post_id, 
						mediaplace_id,
						article_data
					) VALUES (
						'{$post_id}',
						'{$article['dec']->id}',
						'".mysql_real_escape_string(json_encode($article['dec']))."'
					)";
					
					$wpdb->query($sql);
						$ajax = array(
							'success' => true, 
							'message' => null, 
							'article' => $article['dec']
						);
				}
			echo(json_encode($ajax));
			}
		}
	}
exit;
}

// Syndicate article to WordPress site
function wp_oneighty_syndicate_to_site()
{
	global $wpdb, $mp_defs;
	
	$exists = $wpdb->get_results("SELECT mediaplace_id FROM {$wpdb->prefix}mediaplace_posts WHERE mediaplace_id = '{$_POST['id']}'");
	
	if (count($exists)) {
		$return = array(
			'success' => true, 
			'message' => 'This article has already been syndicated.'
		);
	} else {
		$user = wp_oneighty_user_details();
		$post = array(
			'post_status'  => 'publish',
			'post_title'   => 'mediapalce_article:'.strip_tags($_POST['article_id']),
			'post_type'    => 'post',
			'post_content' => 'mediaplace_article:'.strip_tags($_POST['article_id'])
		);
		$new_post_id          = wp_insert_post($post);
		$data                 = array(
			'_method'    => 'fetch', 
			'_key'       => $user->data->account_key, 
			'article_id' => strip_tags($_POST['article_id']), 
		);
		$article              = __json_call($data);
		
		if (isset($article['dec']->error)) {
			return(json_encode(array(
				'success' => false,
				'message' => $article['dec']->error
			)));
		}
		
		$sql = "INSERT INTO {$wpdb->prefix}mediaplace_posts (
			post_id, 
			mediaplace_id, 
			article_data
		) VALUES (
			{$new_post_id},
			{$article['dec']->id}, 
			'".mysql_real_escape_string(json_encode($article['dec']))."'
		)";
			
		if ($wpdb->query($sql)) {
			$user   = wp_oneighty_user_details();
			$data   = array(
				'_method'    => 'syndicate_plus_one', 
				'_key'       => $user->data->account_key, 
				'article_id' => $article['dec']->id, 
				'user_id'    => $user->data->id
			);

			__json_call($data);
			$return = array(
				'success' => true,
				'message' => 'The article was successfully syndicated to your site.'
			);
		} else {
			$return = array(
				'success'   => false,
				'message'   => 'There was an error while attempting to syndicate this article.',
				'statement' => $sql
			);
		}
	}

return(print(json_encode($return)));
}

function wp_oneighty_post_title($title)
{
	global $mp_defs;
	
	if(wp_oneighty_is_article(get_the_ID())) {
		$article = wp_oneighty_article_info(get_the_ID());
		$title   = $article->data->title;
	}
return($title);
}

function wp_oneighty_post_content($content)
{
	global $mp_defs;
	
	if (wp_oneighty_is_article(get_the_ID())) {
		$me       = wp_oneighty_user_details();
		$article  = wp_oneighty_article_info(get_the_ID());
		$author   = __json_call(array(
			'_method' => 'fetch_user', 
			'_key'    => $me->data->account_key, 
			'user_id' => $article->data->author_id
		));
		$content  = "<p><em>By </em><strong>";
		$content .= "<a href=\"https://www.180create.com/user/{$author['dec']->display_name}\">";
		$content .= (isset($author['dec']->first_name) ? "{$author['dec']->first_name} {$author['dec']->last_name}" : "{$author['dec']->display_name}");
		$content .= "</a>";
		$content .= "</strong><em>, Originally Published at </em><a href=\"{$article->data->from_url}\">{$article->data->from_url}</a></p>";
		$content .= $article->data->content;
		$content .= "<br><br><center><a href=\"{$mp_defs['base_url']}\">";
		$content .= "<img src=\"{$mp_defs['base_url']}images/syndicated.png\" alt=\"Syndicated at {$mp_defs['app_name']}\">";
		$content .= "</a></center><br><br>";
	}
return($content);
}

function wp_oneighty_post_author($author)
{
	global $wpdb, $mp_defs;
	
	if (wp_oneighty_is_article(get_the_ID())) {
		$article = wp_oneighty_article_info(get_the_ID());
		$author = $article->data->author_name;
	}
return($author);
}

function wp_oneighty_post_author_link($link)
{
	global $mp_defs;
	
	if (wp_oneighty_is_article(get_the_ID())) {
		$article = wp_oneighty_article_info(get_the_ID());
		$link    = $mp_defs['base_url'].$article->data->author_name;
	}
return($link);
}

function wp_oneighty_post_date($date)
{
	global $wpdb, $mp_defs;
	
	if (wp_oneighty_is_article(get_the_ID())) {
		$article = wp_oneighty_article_info(get_the_ID());
		$date    = date('F j, Y', strtotime($article->data->date_created));
	}
return($date);
}

function wp_oneighty_comment_handler($comments)
{
	global $wpdb, $mp_defs, $current_user;
		get_currentuserinfo();
	
	if (wp_oneighty_is_article(get_the_ID())) {
		$user    = wp_oneighty_user_details();
		$id      = wp_oneighty_article_info(get_the_ID());
		$id      = $id->mediaplace_id;
						
		$data    = array(
			'_method'    => 'grab_comments', 
			'_key'       => $user->data->account_key, 
			'article_id' => strip_tags($id)
		);
		$cmnts = __json_call($data);
		
		if (isset($cmnts['dec']->error)) {
			$error = $cmnts['dec']->error;
		}
		
		require_once($mp_defs['template_path'].'/comments-array.tpl');
	}
return($comments);
}

/* 
 * START TEMPLATE FUNCTIONS 
 */

/*
 * Rather self explainatory, it loads our own flavor of comments.php, only we like to use .tpl files
 * cause we are awesome like that ;-)
 */
function wp_oneighty_comment_template_handler($tpl)
{
	global $wpdb, $mp_defs, $current_user;
		get_currentuserinfo();
	
	if (wp_oneighty_is_article(get_the_ID())) {
		$tpl = $mp_defs['template_path'].'/comments.tpl';
	}
return($tpl);
}

/*
 * Show Comments
 */
function wp_oneighty_comment_show($comment) 
{
	global $mp_defs, $current_user;
		get_currentuserinfo();
			require($mp_defs['template_path'].'/comments/show.tpl');
}

/*
 * Show Commenter's Avatar
 */
 function wp_oneighty_comment_load_avatar($comment, $size)
 {
 	
 }
 
 /*
  * Special Stuff
  */
 
 /*
  * 180Create Ajaxer
  */
 function wp_oneighty_jaxer()
 {
 	global $wpdb, $mp_defs, $current_user;
		get_currentuserinfo();
	
 	require_once($mp_defs['class_path'].'/xmlrpc/lib/xmlrpc.inc');
	
	$account = wp_oneighty_user_details();
	$ajax    = array(
		'data'         => 'There was an error while contacting the MediaPlace server.  Please try again later.', 
		'supplemental' => array(
			'message_type' => 'error'
		)
	);
	
	if ($_POST['route'] == 'oneighty_submit_comment') { 
		$details = array(
			'_method'      => 'post_comment', 
			'_key'         => $account->data->account_key, 
			'article_id'   => strip_tags($_POST['article_id']), 
			'author_id'    => strip_tags($_POST['author_id']), 
			'author_name'  => strip_tags($_POST['author_name']), 
			'author_email' => strip_tags($_POST['author_email']), 
			'author_url'   => strip_tags($_POST['author_url']), 
			'author_ip'    => strip_tags($_POST['author_ip']), 
			'content'      => $_POST['content'], 
			'parent_id'    => 0, 
			'site_id'      => 0, 
			'from_blog'    => get_bloginfo('name'), 
			'from_url'     => get_bloginfo('url')
		);
		
		$posted = __json_call($details);
				
		if ($posted['dec']->success) {
			$ajax['data']                        = 'Comment Added';
			$ajax['supplemntal']['message_type'] = 'success';
		} else {
			$ajax['data']                         = 'There was an error adding your comment.';
			$ajax['supplemental']['message_type'] = 'error';
		}
	echo(json_encode($ajax));
	}
		
	elseif ($_POST['route'] == 'oneighty_comments_grab') { 
		if (wp_oneighty_is_article(strip_tags($_POST['article_id']))) {
			$id      = $wpdb->get_results("SELECT mediaplace_id FROM {$wpdb->prefix}mediaplace_posts WHERE post_id = '".strip_tags($_POST['article_id'])."'");
			$id      = $id[0]->mediaplace_id;
			$data    = array(
				'_method'    => 'grab_comments', 
				'_key'       => $account->data->account_key, 
				'article_id' => strip_tags($id)
			);
			$cmnts   = __json_call($data);
				
			if (isset($cmnts['dec']->error)) {
				$ajax['data']                         = $cmnts['dec']->error;
				$ajax['supplemental']['message_type'] = 'error';
			} else {
				require_once($mp_defs['template_path'].'/comments-array.tpl');
				
				foreach ($comments as $comment) {
					wp_oneighty_comment_show($comment);
				}
			}
		}
	}
	
	elseif ($_POST['route'] == 'oneighty_user_login') {
		$token = (string) sha1(time()).md5(time()).crypt(time());
		
		if ('wordpress' == $_POST['method']) {
			$user = wp_signon(array(
				'user_login'    => $_POST['uname'], 
				'user_password' => $_POST['passwd'], 
				'remember'      => false), 
			false);
			
			if (is_wp_error($user)) {
				$ajax['success'] = false;
				$ajax['error']   = $user->get_error_message();
			} else {
				$_SESSION['mediaplace_auth'] = new stdClass();
				$_SESSION['mediaplace_auth']->token = "{$token}";

				if (null == $user->data->first_name && null == $user->data->last_name) {
					$_SESSION['mediaplace_auth']->name = $user->data->user_login;
				} else {
					$_SESSION['mediaplace_auth']->name = "{$user->data->first_name} {$user->data->last_name}";
				}
				
				$_SESSION['mediaplace_auth']->email = "{$user->data->user_email}";
				$_SESSION['mediaplace_auth']->url   = "{$user->data->user_url}";
				$ajax['success']                    = true;
				$ajax['user']                       = $_SESSION['mediaplace_auth'];
			}
		}
		
		if ('mediaplace' == $_POST['method']) {			
			$data = array(
				'_method'  => 'logon', 
				'username' => strip_tags($_POST['uname']), 
				'passwd'   => strip_tags($_POST['passwd'])
			);
			$user = __json_call($data);
					
			if (isset($user['dec']->error)) {
				$ajax['success']                      = false;
				$ajax['error']                        = $user['dec']->error;
			} else {
				$_SESSION['mediaplace_auth'] = new stdClass();
					$_SESSION['mediaplace_auth']->token = "{$token}";
					$_SESSION['mediaplace_auth']->name  = "{$user['dec']->first_name} {$user['dec']->last_name}";
					$_SESSION['mediaplace_auth']->email = "{$user['dec']->email_address}";
					$_SESSION['mediaplace_auth']->url   = "{$mp_defs['base_url']}{$user['dec']->display_name}";
					$_SESSION['mediaplace_auth']->id    = "{$user['dec']->id}";
				$ajax['success'] = true;	
				$ajax['user']    = $_SESSION['mediaplace_auth'];
			}
		}
	echo(json_encode($ajax));
	}
	
	elseif ($_POST['route'] == 'oneighty_user_auth') {
		if (isset($_SESSION['mediaplace_auth']) && null != $_SESSION['mediaplace_auth']->token) {
			$ajax['success'] = true;
			$ajax['user']    = $_SESSION['mediaplace_auth'];
		} else {
			$ajax['success'] = false;
		}
	echo(json_encode($ajax));
	}

	elseif ($_POST['route'] == 'remove_article_from_site') {
		$article = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE ID = '".strip_tags($_POST['id'])."'");
		$article = $article[0];
			$wpdb->query("DELETE FROM {$wpdb->prefix}mediaplace_posts WHERE post_id = '".strip_tags($_POST['id'])."'");
			
			if (preg_match('/mediaplace_article:/', $article->post_content)) {
				$wpdb->query("DELETE FROM {$wpdb->prefix}posts WHERE ID = '".strip_tags($_POST['id'])."'");
			}
	echo(true);
	}
	
	elseif ($_POST['route'] == 'oneighty_subcategories_grab') {
		echo(wp_oneighty_subcategory_select($_POST['category_id']));
	}
	
	elseif ($_POST['route'] == 'oneighty_syndicate_existing') {
		$ajax = array();
		if (isset($_POST['article_id'])) {
			$_article = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE ID = ".strip_tags($_POST['article_id']));
			$_article = $_article[0];
			$_taga    = preg_replace('/[^A-Za-z0-9_\-]/', '', strip_tags($_POST['tag_word_a']));
			$_tagb    = preg_replace('/[^A-Za-z0-9_\-]/', '', strip_tags($_POST['tag_word_b']));
			$details  = array(
				'_method'              => 'post', 
				'_key'                 => $account->data->account_key, 
				'author_id'            => $account->data->id,
				'content'              => $_article->post_content,
				'title'                => strip_tags($_article->post_title),
				'description'          => strip_tags($_article->post_excerpt),
				'category_id'          => strip_tags($_POST['category_id']),
				'secondcategory_id'    => strip_tags($_POST['secondcategory_id']),
				'group_id'             => 0, 
				'private'              => 0, 
				'tag_words'            => "{$taga}:{$tagb}",
				'cost'                 => 0.00, 
				'allow_free'           => 1,
				'name'                 => preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($_article->post_title))),
				'from_blog'            => get_bloginfo('name'), 
				'from_url'             => get_bloginfo('url')
			);
			$article = __json_call($details);
			
			if (isset($article['dec']->error)) {
				$ajax['success'] = false;
			} else {
				$sql     = "INSERT INTO {$wpdb->prefix}mediaplace_posts (
					post_id, 
					mediaplace_id,
					article_data
				) VALUES (
					'".strip_tags($_POST['article_id'])."',
					'{$article['dec']->id}',
					'".mysql_real_escape_string(json_encode($article['dec']))."'
				)";
				
				if ($wpdb->query($sql)) {
					if ($_POST['comments']) {
						$comments = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}comments WHERE comment_post_ID = ".strip_tags($_POST['article_id'])." AND comment_approved = 1");
						
						foreach ($comments as $cmnt) {
							$details = array(
								'_method'      => 'post_comment', 
								'_key'         => $account->data->account_key, 
								'article_id'   => $article['dec']->id, 
								'author_id'    => $acocunt->data->id, 
								'author_name'  => $cmnt->comment_author, 
								'author_email' => $cmnt->comment_author_email, 
								'author_url'   => $cmnt->comment_author_url, 
								'author_ip'    => $cmnt->comment_author_IP, 
								'content'      => $cmnt->comment_content, 
								'parent_id'    => $cmnt->comment_parent, 
								'site_id'      => 0, 
								'from_blog'    => get_bloginfo('name'), 
								'from_url'     => get_bloginfo('url')
							);
							$posted  = __json_call($details);
						}
						$ajax['success'] = true;
					} else {
						$ajax['success'] = true;
					}
				} else {
					$ajax['success'] = false;
				}
			}
		} else {
			$ajax['success'] = false;
		}
		echo(json_encode($ajax));
	}
exit;
}

function __json_call($_data)
{
	global $mp_defs;
	
	$_context = stream_context_create(array(
		'http' => array (
			'method'  => 'POST', 
		  	'header'  => 'Content-type: application/json', 
			'content' => json_encode($_data)
		)
	));
	
	$_request  = file_get_contents($mp_defs['jsonrpc_url'], false, $_context);

	$_response = array(
		'enc' => stripslashes($_request), 
		'dec' => json_decode($_request)
	);
	return($_response);
}

function __log_oneighty_actions(array $_data)
{
	global $wpdb, $mp_defs;
		
	if ($wpdb->query("INSERT INTO {$wpdb->prefix}mediaplace_log (raw, created) VALUES ('".mysql_real_escape_string(json_encode($_data))."', NOW())")) {
		return(true);
	} else {
		return(false);
	}
}
?>
