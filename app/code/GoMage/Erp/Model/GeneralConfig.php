<?php

declare(strict_types=1);

namespace GoMage\Erp\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class GeneralConfig
{
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getApiKey(): string
    {
        return (string) $this->scopeConfig->getValue('erp/general/api_key');
    }
}
