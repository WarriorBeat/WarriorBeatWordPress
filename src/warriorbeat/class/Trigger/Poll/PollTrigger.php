<?php

/**
 * PollTrigger
 * Abstract Notification triggers for WP-Polls
 * 
 * @package warriorbeat
 * 
 */

namespace BracketSpace\Notification\WarriorBeat\Trigger\Poll;

abstract class PollTrigger extends \BracketSpace\Notification\Abstracts\Trigger
{

    /**
     * Constructor
     */
    public function __construct($slug, $name)
    {

        /**
         * Constructor
         *
         * @param string $slug $params trigger slug.
         * @param string $name $params trigger name.
         */
        parent::__construct($slug, $name);
        $this->set_group(__('Poll', 'notification'));

    }


    /**
     * Registers attached merge tags
     *
     * @return void
     */
    public function merge_tags()
    {


        $this->add_merge_tag(new \BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
            'slug' => 'poll_question',
            'name' => __('Poll Question', 'Inserts Poll Question.'),
            'resolver' => function ($trigger) {
                return get_poll_question($trigger->param);
            },
        )));

    }

}
