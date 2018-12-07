<?php

/**
 * PollVoted
 * Notification Trigger for voting on a Poll
 * 
 * @package warriorbeat
 * 
 */

namespace BracketSpace\Notification\WarriorBeat\Trigger\Poll;

class PollVoted extends PollTrigger
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'warriorbeat/poll_voted',
            __('Poll Voted', 'notification')
        );

        $this->add_action('wb_poll_voted', 10, 2);
        $this->set_description(
            __('Fires when a poll is voted on', 'notification')
        );

    }

    /**
     * Assigns action callback args to object
     *
     * @return mixed void or false if no notifications should be sent
     */
    public function action($param, $process)
    {
        if (false === $process) {
            return false;
        }

        $this->poll = $param;
        $poll_answers = $this->poll['answers'];
        foreach ($poll_answers as $key => $val) {
            $ans_id = $poll_answers[$key]->polla_aid;
            $ans = $poll_answers[$key]->polla_answers;
            $ans_votes = $poll_answers[$key]->polla_votes;

            $poll_answers[$key] = array(
                'answerId' => $ans_id,
                'answer' => $ans,
                'votes' => $ans_votes
            );
        };
        $this->poll['answers'] = $poll_answers;
    }

}
