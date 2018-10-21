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

function nest_author($args, $key, $trigger)
{
	$user = get_userdata($trigger->post->post_author);
	$author = $user->data;
	$author_meta = get_user_meta($trigger->post->post_author);
	$author_data = array(
		'authorId' => $author->ID,
		'name' => $author->display_name,
		'title' => implode(', ', $user->roles),
		'description' => $author_meta->description != null ? $author_meta->description : "Staff Member"
	);
	$author_data['profile_image'] = array(
		'name' => $author->user_nicename,
		'source' => get_avatar_url($author->ID),
		'mediaId' => $author->ID
	);
	$args[$key] = $author_data;
	return $args;
}

function nest_media($source, $id, $title, $credits = '', $caption = '')
{
	$media = array(
		'mediaId' => $id,
		'source' => $source,
		// 'source' => 'http://www.apimages.com/Images/Ap_Creative_Stock_Header.jpg',
		'title' => $title
	);
	return $media;

}

// Merge Tags for Inserting arrays as Nests
function insert_nest($args, $notif, $trigger)
{
	foreach ($args as $key => $val) {
		if ((string)$val == 'wb_nested_author') {
			$args = nest_author($args, $key, $trigger);
		}
		if ((string)$val == 'wb_featured_media') {
			$thumb_url = get_the_post_thumbnail_url($trigger->post->ID);
			$thumb_caption = get_the_post_thumbnail_caption($trigger->post->ID);
			$thumb_id = get_post_thumbnail_id($trigger->post->ID);
			$thumb_title = $trigger->post->post_title;
			$args[$key] = nest_media($thumb_url, $thumb_id, $thumb_title, '', $thumb_caption);
		}
	}
	return $args;
}
add_filter('notification/webhook/args', 'insert_nest', 10, 3);


// Trigger Hooks
add_action('notification/trigger/registered', function ($trigger) {
	$trig_slugs = array(
		"wordpress/post/published",
		"wordpress/post/updated"
	);
	if (!in_array($trigger->get_slug(), $trig_slugs)) {
		return;
	}

	$trigger->add_merge_tag(new BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
		'slug' => 'nested_author',
		'name' => __('Nested Author Data', 'Inserts author data to post request.'),
		'resolver' => function ($trigger) {
			return 'wb_nested_author';
		},
	)));

	$trigger->add_merge_tag(new BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
		'slug' => 'post_featured_media',
		'name' => __('Post Featured Media', 'Inserts post thumbnail data.'),
		'resolver' => function ($trigger) {
			return 'wb_featured_media';
		},
	)));
});