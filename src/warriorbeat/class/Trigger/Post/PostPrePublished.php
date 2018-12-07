<?php

/**
 * PostPrePublish
 * Trigger just before PostPublish Trigger
 * 
 * @package warriorbeat
 * 
 */

namespace BracketSpace\Notification\WarriorBeat\Trigger\Post;

use BracketSpace\Notification\Defaults\MergeTag;

class PostPrePublished extends \BracketSpace\Notification\Defaults\Trigger\Post\PostTrigger
{

    /**
     * Constructor
     */
    public function __construct($post_type = 'post')
    {
        parent::__construct(
            array(
                'post_type' => $post_type,
                'slug' => 'wordpress/' . $post_type . '/prepublished',
				// translators: singular post name.
                'name' => sprintf(__('%s pre-published', 'notification'), parent::get_post_type_name($post_type)),
            )
        );

        $this->add_action('new_to_publish', 9);
        $this->add_action('auto-draft_to_publish', 9);
        $this->add_action('draft_to_publish', 9);
        $this->add_action('pending_to_publish', 9);
        $this->add_action('private_to_publish', 9);
        $this->add_action('future_to_publish', 9);
        $this->set_description(
            __('Fires just before PostPublished trigger', 'notification')
        );

    }

    /**
     * Assigns action callback args to object
     *
     * @param object $post Post object.
     * @return mixed void or false if no notifications should be sent
     */
    public function action($post)
    {
        $this->{$this->post_type} = $post;
        if ($this->{$this->post_type}->post_type !== $this->post_type) {
            return false;
        }
        $this->author = get_userdata($this->{$this->post_type}->post_author);
        $this->publishing_user = get_userdata(get_current_user_id());
        $this->{$this->post_type . '_creation_datetime'} = strtotime($this->{$this->post_type}->post_date);
        $this->{$this->post_type . '_modification_datetime'} = strtotime($this->{$this->post_type}->post_modified);
		// Postpone the action to make sure all the meta has been saved.
        if (function_exists('acf')) {
            $postponed_action = 'acf/save_post';
        } else {
            $postponed_action = 'save_post';
        }
        $this->postpone_action($postponed_action, 1000);
    }
    /**
     * Postponed action callback
     * Return `false` if you want to abort the trigger execution
     *
     * @return mixed void or false if no notifications should be sent
     */
    public function postponed_action()
    {
        if (function_exists('acf')) {
            return;
        }
		// fix for the action being called twice by WordPress.
        if (did_action('save_post') > 1) {
            return false;
        }
    }
    /**
     * Registers attached merge tags
     *
     * @return void
     */
    public function merge_tags()
    {
        $post_name = parent::get_post_type_name($this->post_type);
        parent::merge_tags();
		// Publishing user.
        $this->add_merge_tag(
            new MergeTag\User\UserID(
                array(
                    'slug' => $this->post_type . '_publishing_user_ID',
					// translators: singular post name.
                    'name' => sprintf(__('%s publishing user ID', 'notification'), $post_name),
                    'property_name' => 'publishing_user',
                )
            )
        );
        $this->add_merge_tag(
            new MergeTag\User\UserLogin(
                array(
                    'slug' => $this->post_type . '_publishing_user_login',
					// translators: singular post name.
                    'name' => sprintf(__('%s publishing user login', 'notification'), $post_name),
                    'property_name' => 'publishing_user',
                )
            )
        );
        $this->add_merge_tag(
            new MergeTag\User\UserEmail(
                array(
                    'slug' => $this->post_type . '_publishing_user_email',
					// translators: singular post name.
                    'name' => sprintf(__('%s publishing user email', 'notification'), $post_name),
                    'property_name' => 'publishing_user',
                )
            )
        );
        $this->add_merge_tag(
            new MergeTag\User\UserNicename(
                array(
                    'slug' => $this->post_type . '_publishing_user_nicename',
					// translators: singular post name.
                    'name' => sprintf(__('%s publishing user nicename', 'notification'), $post_name),
                    'property_name' => 'publishing_user',
                )
            )
        );
        $this->add_merge_tag(
            new MergeTag\User\UserDisplayName(
                array(
                    'slug' => $this->post_type . '_publishing_user_display_name',
					// translators: singular post name.
                    'name' => sprintf(__('%s publishing user display name', 'notification'), $post_name),
                    'property_name' => 'publishing_user',
                )
            )
        );
        $this->add_merge_tag(
            new MergeTag\User\UserFirstName(
                array(
                    'slug' => $this->post_type . '_publishing_user_firstname',
					// translators: singular post name.
                    'name' => sprintf(__('%s publishing user first name', 'notification'), $post_name),
                    'property_name' => 'publishing_user',
                )
            )
        );
        $this->add_merge_tag(
            new MergeTag\User\UserLastName(
                array(
                    'slug' => $this->post_type . '_publishing_user_lastname',
					// translators: singular post name.
                    'name' => sprintf(__('%s publishing user last name', 'notification'), $post_name),
                    'property_name' => 'publishing_user',
                )
            )
        );
    }
}
