<?php

/**
 * PollAdded
 * Notification Trigger for Adding a new Poll
 * 
 * @package warriorbeat
 * 
 */

namespace BracketSpace\Notification\WarriorBeat\Trigger\Poll;

class PollAdded extends PollTrigger
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'warriorbeat/poll_added',
            __('Poll Added', 'notification')
        );

        $this->add_action('wb_poll_added', 10, 2);
        $this->set_description(
            __('Fires when a new poll has been added', 'notification')
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

        $this->param = $param;

    }

}
