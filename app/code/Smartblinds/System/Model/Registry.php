<?php declare(strict_types=1);

namespace Smartblinds\System\Model;

class Registry
{
    private System $system;

    public function getSystem(): System
    {
        return $this->system;
    }

    public function setSystem(System $system)
    {
        $this->system = $system;
    }
}
