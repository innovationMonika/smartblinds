<?php

namespace Smartblinds\Options\Model\System\Config\Source;

use Magento\Eav\Model\Config;
use Magento\Framework\Data\OptionSourceInterface;

class SystemType implements OptionSourceInterface
{
    protected $eavConfig;

    public function __construct(Config $eavConfig)
    {
        $this->eavConfig = $eavConfig;
    }

    public function toOptionArray()
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'system_type');
        $options = $attribute->getSource()->getAllOptions();

        // Removing the empty option which is typically the first one
        array_shift($options);

        return $options;
    }
}
