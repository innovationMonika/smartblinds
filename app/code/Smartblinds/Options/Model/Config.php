<?php declare(strict_types=1);

namespace Smartblinds\Options\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getMeasurementMessage(): string
    {
        return $this->scopeConfig->getValue(
            'smartblinds_options/messages/measurement',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getSystemTypeTdbuOptionId()
    {
        return $this->scopeConfig->getValue(
            'smartblinds_options/general/system_type_tdbu',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getBedieningOptionCode()
    {
        return $this->scopeConfig->getValue(
            'smartblinds_options/general/bediening_option_code',
            ScopeInterface::SCOPE_STORE
        );
    }
}
