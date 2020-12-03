<?php

namespace Acelle\Jobs;

class ExportCampaignLog extends SystemJob
{
    protected $campaign;
    protected $logtype;

    /**
     * Create a new job instance.
     * @note: Parent constructors are not called implicitly if the child class defines a constructor.
     *        In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     *
     * @return void
     */
    public function __construct($campaign, $logtype)
    {
        $this->campaign = $campaign;
        $this->logtype = $logtype;

        parent::__construct();

        // This line must go after the constructor
        $this->updateStatus([ 'campaign_id' => $campaign->id, 'progress' => 0 ]);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->campaign->generateTrackingLogCsv($this->logtype, function($progress) {
            $this->updateStatus($progress);
        });
    }

    /**
     * Get import file name.
     *
     * @return void
     */
    public function updateStatus($data)
    {
        $systemJobModel = $this->getSystemJob();
        $json = ($systemJobModel->data) ? json_decode($systemJobModel->data, true) : [];
        $systemJobModel->data = json_encode(array_merge($json, $data));
        $systemJobModel->save();
    }
}
