<?php

namespace Acelle\Library\Automation;

class Operate extends Action
{
    public function execute()
    {
        // IMPORTANT
        // If this is the latest also the last action of the workflow
        // no more execute, just return true
        if (!is_null($this->last_executed)) {
            $this->autoTrigger->logger()->info('Already executed, I\'m the latest also the last action');

            return true;
        }

        sleep(1); // to avoid same date/time with previous wait, wrong timeline order

        if (config('app.demo') == true) {
            // do nothing
        } else {
            dispatch(new DeliverEmail($email, $this->autoTrigger->subscriber));
        }

        $this->autoTrigger->logger()->info(sprintf('Perform an action...'));

        $this->recordLastExecutedTime();
        $this->evaluationResult = true;

        return true;
    }

    // Overwrite
    public function getActionDescription()
    {
        $nameOrEmail = $this->autoTrigger->subscriber->getFullNameOrEmail();

        return sprintf('Perform an operation');
    }
}
