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
            'slug' => 'poll_id',
            'name' => __('Poll ID', 'Inserts Poll ID.'),
            'resolver' => function ($trigger) {
                return $trigger->poll['id'];
            },
        )));


        $this->add_merge_tag(new \BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
            'slug' => 'poll_question',
            'name' => __('Poll Question', 'Inserts Poll Question.'),
            'resolver' => function ($trigger) {
                return $trigger->poll['question'];
            },
        )));

        $this->add_merge_tag(new \BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
            'slug' => 'poll_total_votes',
            'name' => __('Poll Total Votes', 'Inserts Total Votes of a Poll.'),
            'resolver' => function ($trigger) {
                return $trigger->poll['pollq_totalvotes'];
            },
        )));

        $this->add_merge_tag(new \BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
            'slug' => 'poll_status',
            'name' => __('Poll Status', 'Inserts Poll Status.'),
            'resolver' => function ($trigger) {
                return $trigger->poll['status'];
            },
        )));

        $this->add_merge_tag(new \BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
            'slug' => 'poll_date',
            'name' => __('Poll Create Date', 'Inserts Poll Creation Date.'),
            'resolver' => function ($trigger) {
                return $trigger->poll['date'];
            },
        )));

        $this->add_merge_tag(new \BracketSpace\Notification\Defaults\MergeTag\StringTag(array(
            'slug' => 'poll_answers',
            'name' => __('Poll Answers', 'Inserts Poll Answers.'),
            'resolver' => function ($trigger) {
                return 'wb_nested_poll_answers';
            },
        )));


    }

}
