<?php

namespace Acelle\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Acelle\Model\Automation2;
use Acelle\Library\Log as MailLog;

class UpdateAutomation extends SystemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $automation;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Automation2 $automation)
    {
        $this->automation = $automation;
        parent::__construct();

        // This line must go after the constructor
        $this->linkJobToAutomation();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function linkJobToAutomation()
    {
        $systemJob = $this->getSystemJob();
        $systemJob->data = $this->automation->id;
        $systemJob->save();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->automation->updateCache();
    }
}
