<?php

namespace Smartblinds\Checkout\Helper;

use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Smartblinds\System\Model\Config as SystemConfig;

class ProductAttribute extends AbstractHelper
{
    private Repository $attributeRepository;
    private SystemConfig $systemConfig;

    public function __construct(
        Context $context,
        Repository $attributeRepository,
        SystemConfig $systemConfig
    ) {
        parent::__construct($context);
        $this->attributeRepository = $attributeRepository;
        $this->systemConfig = $systemConfig;
    }

    public function getAttribute($code)
    {
        try {
            return $this->attributeRepository->get($code);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function isChainCustomerGroup()
    {
        return (bool) $this->systemConfig->isShowControlType();
    }
}
