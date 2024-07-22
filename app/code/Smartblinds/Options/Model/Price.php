<?php

namespace Smartblinds\Options\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Helper\Price as BasePriceHelper;
use MageWorx\OptionFeatures\Model\ResourceModel\BundleSelected;

class Price extends \MageWorx\OptionFeatures\Model\Price
{
    private AttributeRepositoryInterface $attributeRepository;
    private Config $config;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        DataObject $specialPriceModel,
        DataObject $tierPriceModel,
        ManagerInterface $eventManager,
        BaseHelper $baseHelper,
        BasePriceHelper $basePriceHelper,
        ObjectManagerInterface $objectManager,
        BundleSelected $bundleSelected,
        AttributeRepositoryInterface $attributeRepository,
        Config $config
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->config = $config;
        parent::__construct($productRepository, $specialPriceModel, $tierPriceModel, $eventManager,
            $baseHelper, $basePriceHelper, $objectManager, $bundleSelected);
    }

    protected function getValueQty($option, $value, $infoBuyRequest)
    {
        if ($this->isBeidingOption($option) && $this->isTdbuSelected($infoBuyRequest)) {
            return 2;
        }

        $valueQty = 1;
        if (!empty($infoBuyRequest['options_qty'][$option->getOptionId()][$value->getOptionTypeId()])) {
            $valueQty = $infoBuyRequest['options_qty'][$option->getOptionId()][$value->getOptionTypeId()];
        } elseif (!empty($infoBuyRequest['options_qty'][$option->getOptionId()])) {
            $valueQty = $infoBuyRequest['options_qty'][$option->getOptionId()];
        }

        return $valueQty;
    }

    private function isBeidingOption($option)
    {
        return $option->getData('option_code') == $this->config->getBedieningOptionCode();
    }

    private function isTdbuSelected($infoBuyRequest)
    {
        $systemTypeAttributeId = $this->attributeRepository->get('catalog_product', 'system_type')->getAttributeId();
        $systemTypeOptionTdbuId = $this->config->getSystemTypeTdbuOptionId();
        return ($infoBuyRequest['super_attribute'][$systemTypeAttributeId] ?? '-1') == $systemTypeOptionTdbuId;
    }

}
