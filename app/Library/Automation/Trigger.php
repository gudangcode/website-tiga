<?php

namespace Acelle\Library\Automation;

class Trigger extends Action
{
    public function execute()
    {
        $this->recordLastExecutedTime();
        $this->evaluationResult = true;

        return true;
    }

    public function getActionDescription()
    {
        $nameOrEmail = $this->autoTrigger->subscriber->getFullNameOrEmail();

        return sprintf('User %s subscribes to mail list, automation triggered!', $nameOrEmail);
    }
}
