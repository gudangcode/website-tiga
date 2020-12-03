<?php

namespace Acelle\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Acelle\Events\CampaignUpdated' => [
            'Acelle\Listeners\CampaignUpdatedListener',
        ],
        'Acelle\Events\MailListUpdated' => [
            'Acelle\Listeners\MailListUpdatedListener',
        ],
        'Acelle\Events\UserUpdated' => [
            'Acelle\Listeners\UserUpdatedListener',
        ],
        'Acelle\Events\CronJobExecuted' => [
            'Acelle\Listeners\CronJobExecutedListener',
        ],
        'Acelle\Events\AdminLoggedIn' => [
            'Acelle\Listeners\AdminLoggedInListener',
        ],
        'Acelle\Events\MailListSubscription' => [
            'Acelle\Listeners\MailListSubscriptionListener',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
