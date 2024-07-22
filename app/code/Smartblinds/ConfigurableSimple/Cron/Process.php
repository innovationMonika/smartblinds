<?php

namespace Smartblinds\ConfigurableSimple\Cron;

use Smartblinds\ConfigurableSimple\Model\Update;

class Process
{
    private Update $update;

    public function __construct(Update $update)
    {
        $this->update = $update;
    }

    public function execute()
    {
        $this->update->execute();
    }
}
