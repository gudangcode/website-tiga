<?php

namespace Acelle\Events;

use Acelle\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MailListSubscription extends Event
{
    use SerializesModels;
    
    public $user;
    public $subscriber;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, $subscriber)
    {
        $this->user = $user;
        $this->subscriber = $subscriber;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
