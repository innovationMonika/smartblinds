<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Helper;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Helper\FilterSetting as BaseFilterSetting;
use Amasty\ShopbyBase\Model\FilterSetting\FilterResolver;
use Amasty\ShopbyBase\Model\FilterSettingFactory;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Store\Model\ScopeInterface;

class FilterSetting extends BaseFilterSetting
{
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        FilterSettingFactory $settingFactory,
        FilterResolver $filterResolver,
        ScopeConfigInterface $scopeConfig,
        BlockFactory $blockFactory
    ) {
        parent::__construct($settingFactory, $filterResolver, $scopeConfig);
        $this->blockFactory = $blockFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function getSettingByLayerFilter(FilterInterface $layerFilter): ?FilterSettingInterface
    {
        $attributeCode = $this->getFilterCode($layerFilter);
        $setting = $this->getFilterSettingByCode($attributeCode);

        if ($setting !== null) {
            $setting->setAttributeModel($layerFilter->getData('attribute_model'));
        }

        return $setting;
    }

    /**
     * @param FilterInterface $layerFilter
     * @return string|null
     */
    public function getFilterCode(FilterInterface $layerFilter)
    {
        $attribute = $layerFilter->getData('attribute_model');
        $filterCode = is_object($attribute) ? $attribute->getAttributeCode() : null;

        if (!$filterCode) {
            if ($layerFilter instanceof \Amasty\Shopby\Model\Layer\Filter\Category) {
                $filterCode = \Amasty\Shopby\Helper\Category::ATTRIBUTE_CODE;
            } elseif ($layerFilter instanceof \Amasty\Shopby\Model\Layer\Filter\Stock) {
                $filterCode = 'stock';
            } elseif ($layerFilter instanceof \Amasty\Shopby\Model\Layer\Filter\Rating) {
                $filterCode = 'rating';
            } elseif ($layerFilter instanceof \Amasty\Shopby\Model\Layer\Filter\IsNew) {
                $filterCode = 'am_is_new';
            } elseif ($layerFilter instanceof \Amasty\Shopby\Model\Layer\Filter\OnSale) {
                $filterCode = 'am_on_sale';
            }
        }

        return $filterCode;
    }

    /**
     * @return string
     */
    public function getShowMoreButtonBlock($setting)
    {
        return $this->blockFactory->createBlock(\Amasty\Shopby\Block\Navigation\Widget\HideMoreOptions::class)
            ->setFilterSetting($setting);
    }

    /**
     * @param string $path
     * @return bool
     * @deprecared
     */
    public function isSetConfig($path)
    {
        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }
}
