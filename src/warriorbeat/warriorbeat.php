<?php

/**
 * Plugin Name: WarriorBeat
 * Description: WarriorBeat Plugin for Webhooks and other stuff
 * Plugin URI: https://localhost
 * Author: WarriorBeat
 * Author URI: https://localhost
 * Version: 1.0.0
 * License: GPL3
 * Text Domain: warriorbeat
 * Domain Path: /languages
 *
 * @package warriorbeat
 */


/**
 * Plugin's autoload function
 *
 * @param  string $class class name.
 * @return mixed         false if not plugin's class or void
 */
function warriorbeat_autoload($class)
{

	$parts = explode('\\', $class);

	if (array_shift($parts) !== 'BracketSpace') {
		return false;
	}

	if (array_shift($parts) !== 'Notification') {
		return false;
	}

	if (array_shift($parts) !== 'WarriorBeat') {
		return false;
	}

	$file = trailingslashit(dirname(__FILE__)) . trailingslashit('class') . implode('/', $parts) . '.php';

	if (file_exists($file)) {
		require_once $file;
	}

}
spl_autoload_register('warriorbeat_autoload');

/**
 * Boot up the plugin in theme's action just in case the Notification
 * is used as a bundle.
 */
add_action('after_setup_theme', function () {

	/**
	 * Requirements check
	 */
	$requirements = new BracketSpace\Notification\WarriorBeat\Utils\Requirements(__('WarriorBeat', 'warriorbeat'), array(
		'php' => '5.3',
		'wp' => '4.6',
		'notification' => true,
	));

	/**
	 * Tests if Notification plugin is active
	 * We have to do it like this in case the plugin
	 * is loaded as a bundle.
	 *
	 * @param string $comparsion value to test.
	 * @param object $r          requirements.
	 * @return void
	 */
	function warriorbeat_check_base_plugin($comparsion, $r)
	{
		if (true === $comparsion && !function_exists('notification_runtime')) {
			$r->add_error(__('Notification plugin active', 'warriorbeat'));
		}
	}

	$requirements->add_check('notification', 'warriorbeat_check_base_plugin');

	if (!$requirements->satisfied()) {
		add_action('admin_notices', array($requirements, 'notice'));
		return;
	}


});
/* Echo variable
 * Description: Uses <pre> and print_r to display a variable in formated fashion
 */
function echo_log($what)
{
	echo '<pre>' . print_r($what, true) . '</pre>';
}

// Retrieves and inserts author information into args
function nest_author($args, $key, $trigger)
{
	$user = get_userdata($trigger->post->post_author);
	$author = $user->data;
	$author_meta = get_user_meta($trigger->post->post_author);
	$author_data = array(
		'authorId' => $author->ID,
		'name' => $author->display_name,
		'title' => $user->roles,
		'description' => $author_meta->description != null ? $author_meta->description : "Staff Member"
	);
	$author_data['profile_image'] = array(
		'name' => $author->user_nicename,
		'source' => get_avatar_url($author->ID, array('size' => 512)),
		'mediaId' => $author->ID
	);
	$args[$key] = $author_data;
	return $args;
}


// Get Post Categories and return array of their data
function nest_categories($post_ID, $id_only = false)
{
	$post_cats = wp_get_post_categories($post_ID);
	$cats = array();

	foreach ($post_cats as $c) {
		$cat = get_category($c);
		if (!$id_only) {
			$cats[] = array(
				'categoryId' => (string)$cat->cat_ID,
				'name' => (string)$cat->name
			);
		} else {
			$cats[] = (string)$cat->cat_ID;
		}
	}
	return $cats;
}

// Merge Tags for Inserting arrays as Nests
function insert_nest($args, $notif, $trigger)
{
	foreach ($args as $key => $val) {
		if (isset($trigger->$val)) {
			$args[$key] = $trigger->$val;
		}
		if ($key == 'JSON_ARRAY') {
			$to_wrap = $args[$key];
			$args = $to_wrap;
		}
	}
	return $args;
}
add_filter('notification/webhook/args', 'insert_nest', 10, 3);


// Post Trigger Hooks
add_action('notification/trigger/registered', function ($trigger) {
	$trig_slugs = array(
		"wordpress/post/published",
		"wordpress/post/updated",
		"wordpress/post/added",
		"wordpress/post/prepublished"
	);
	if (!in_array($trigger->get_slug(), $trig_slugs)) {
		return;
	}

	// ISO Post Creation Date
	$trigger->add_merge_tag(new BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
		'slug' => 'post_create_datetimeiso',
		'name' => __('Post ISO Create Datetime', 'Get Creation date of Post in ISO format.'),
		'resolver' => function ($trigger) {
			return get_the_date('c', $trigger->post->ID);
		},
	)));

	// Nested (Array of) Categories Ids
	$trigger->add_merge_tag(new BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
		'slug' => 'post_category_ids',
		'name' => __('Categories ID List', 'Inserts Post Category ids.'),
		'resolver' => function ($trigger) {
			$trigger->nested_categories = nest_categories($trigger->post->ID, $id_only = true);
			return 'nested_categories';
		},
	)));

	// Nested (Array of) Categories Items
	$trigger->add_merge_tag(new BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
		'slug' => 'post_category_items',
		'name' => __('Category Object List', 'Inserts Post Category data.'),
		'resolver' => function ($trigger) {
			$trigger->nested_categories = nest_categories($trigger->post->ID);
			return 'nested_categories';
		},
	)));

	// Wraps data in Array
	$trigger->add_merge_tag(new BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
		'slug' => 'json_many',
		'name' => __('Wrap JSON in Array', 'Wraps Json Data in Array'),
		'resolver' => function ($trigger) {
			return 'JSON_ARRAY';
		},
	)));

	// Featured Media ID
	$trigger->add_merge_tag(new BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
		'slug' => 'post_featured_media',
		'name' => __('Post Featured Media', 'Inserts post thumbnail data.'),
		'resolver' => function ($trigger) {
			return get_post_thumbnail_id($trigger->post->ID);
		},
	)));

	// Featured Media Direct URL
	$trigger->add_merge_tag(new BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
		'slug' => 'post_featured_direct',
		'name' => __('Post Featured Media Direct URL', 'Inserts post thumbnail url.'),
		'resolver' => function ($trigger) {
			return get_the_post_thumbnail_url($trigger->post->ID);
		},
	)));

	// Featured Media Caption
	$trigger->add_merge_tag(new BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
		'slug' => 'post_featured_caption',
		'name' => __('Post Featured Media Caption', 'Inserts post thumbnail caption.'),
		'resolver' => function ($trigger) {
			return get_the_post_thumbnail_caption($trigger->post->ID);
		},
	)));

	// Featured Media Credits
	$trigger->add_merge_tag(new BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
		'slug' => 'post_featured_credits',
		'name' => __('Post Featured Media Credits', 'Inserts post thumbnail credits.'),
		'resolver' => function ($trigger) {
			return get_post_meta($trigger->attachment->ID, "credit", true);
		},
	)));

	// Featured Media Title
	$trigger->add_merge_tag(new BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
		'slug' => 'post_featured_title',
		'name' => __('Post Featured Media Title', 'Inserts post thumbnail title.'),
		'resolver' => function ($trigger) {
			return $trigger->feat_media->post_title;
		},
	)));

	// Post Author Bio
	$trigger->add_merge_tag(new BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
		'slug' => 'post_author_bio',
		'name' => __('Post author Bio', 'Inserts Post Author Bio.'),
		'resolver' => function ($trigger) {
			$author_meta = get_user_meta($trigger->post->post_author);
			return $author_meta->description != null ? $author_meta->description : "Staff Member";
		},
	)));

	// Post Author Roles
	$trigger->add_merge_tag(new BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
		'slug' => 'post_author_roles',
		'name' => __('Post author Roles', 'Inserts Post Author Roles.'),
		'resolver' => function ($trigger) {
			$user = get_userdata($trigger->post->post_author);
			$trigger->post_author_roles = $user->roles;
			return 'post_author_roles';
		},
	)));

	// Post Author Profile Media Id
	$trigger->add_merge_tag(new BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
		'slug' => 'post_author_profile_image',
		'name' => __('Post author Profile Image ID', 'Inserts Post Profile Image.'),
		'resolver' => function ($trigger) {
			$author_meta = get_user_meta($trigger->post->post_author);
			return $author_meta['wp_metronet_image_id'][0];
		},
	)));


});

// Media Trigger Register
add_action('notification/trigger/registered', function ($trigger) {
	$trig_slugs = array(
		"wordpress/media_published",
		"wordpress/media_updated"
	);
	if (!in_array($trigger->get_slug(), $trig_slugs)) {
		return;
	}
	
	// Media Caption
	$trigger->add_merge_tag(new BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
		'slug' => 'attachment_caption',
		'name' => __('Attachment Caption', 'Inserts Attachment Caption.'),
		'resolver' => function ($trigger) {
			return $trigger->attachment->post_excerpt;
		},
	)));

	// Media Credits
	$trigger->add_merge_tag(new BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
		'slug' => 'attachment_credits',
		'name' => __('Attachment Credits', 'Inserts Attachment Credits.'),
		'resolver' => function ($trigger) {
			return get_post_meta($trigger->attachment->ID, "credit", true);
		},
	)));
});

// On new Poll
add_action('wp_polls_add_poll', function ($pollid) {
	do_action('wb_poll_added', $pollid, true);
});

// On Poll Vote
add_action('wp_polls_vote_poll_success', function () {
	// Get Poll ID
	$poll_id = (int)(isset($_REQUEST['poll_id']) ? (int)sanitize_key($_REQUEST['poll_id']) : 0);
	if ($poll_id === 0) {
		_e('Invalid Poll ID', 'wp-polls');
		exit();
	}
	// Poll Data
	$poll = get_poll_template_by_me($poll_id);
	do_action('wb_poll_voted', $poll, true);
});


// Register Custom Triggers
register_trigger(new BracketSpace\Notification\WarriorBeat\Trigger\Poll\PollAdded());
register_trigger(new BracketSpace\Notification\WarriorBeat\Trigger\Poll\PollVoted());
register_trigger(new BracketSpace\Notification\WarriorBeat\Trigger\Post\PostPrePublished());