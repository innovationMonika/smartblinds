<?php

namespace Smartblinds\OrderStatus\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isLoggingEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('smartblinds_orderstatus/logging/enabled');
    }

    public function isLogBacktrace(): bool
    {
        return $this->scopeConfig->isSetFlag('smartblinds_orderstatus/logging/backtrace');
    }
}
