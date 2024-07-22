<?php

namespace Smartblinds\Tax\Plugin\Model\TaxConfigProvider;

use Magento\Tax\Model\TaxConfigProvider;
use Smartblinds\Tax\Model\Config;

class AddAdditionalTaxSettings
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function afterGetConfig(
        TaxConfigProvider $subject,
        array $result
    ) {
        $result['isHideTax'] = $this->config->isHideTax();
        return $result;
    }
}
