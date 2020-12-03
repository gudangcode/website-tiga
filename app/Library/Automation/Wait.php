<?php

namespace Acelle\Library\Automation;

use Carbon\Carbon;

class Wait extends Action
{
    public function execute()
    {
        if (config('app.demo') == true) {
            $this->evaluationResult = (bool) random_int(0, 1);
            $this->recordLastExecutedTime();

            return $this->evaluationResult;
        }

        $now = Carbon::now();
        $waitDuration = $this->getOption('time');  // 1 hour, 1 day, 2 days
        $parentExecutionTime = $carbon = Carbon::createFromTimestamp($this->getParent()->getLastExecuted());
        $due = $parentExecutionTime->modify($waitDuration);

        $this->evaluationResult = $now->gte($due);
        $this->recordLastExecutedTime();

        if ($this->evaluationResult) {
            sleep(1); // to avoid same day with previous action when modifying (n days)
            $this->autoTrigger->logger()->info(sprintf('---> It is already %s minutes (or %s hours) due! move to next action!', $now->diffInMinutes($due), $now->diffInHours($due)));
        } else {
            $this->autoTrigger->logger()->info(sprintf('---> Waiting for another %s minutes (or %s hours)...', $now->diffInMinutes($due), $now->diffInHours($due)));
        }

        return $this->evaluationResult;
    }

    public function getActionDescription()
    {
        $nameOrEmail = $this->autoTrigger->subscriber->getFullNameOrEmail();

        return sprintf('Wait for 24 hours before proceeding with the next event for user %s', $nameOrEmail);
    }
}
