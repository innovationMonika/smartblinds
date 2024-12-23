<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Helper;

use Amasty\ShopbyBase\Model\FilterSetting\FilterResolver;
use Amasty\ShopbyBase\Model\FilterSettingFactory;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Magento\Store\Model\ScopeInterface;

class FilterSetting
{
    public const ATTR_PREFIX = 'attr_';

    /**
     * @var  FilterSettingFactory
     */
    protected $settingFactory;

    /**
     * @var FilterResolver
     */
    private $filterResolver;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        FilterSettingFactory $settingFactory,
        FilterResolver $filterResolver,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->settingFactory = $settingFactory;
        $this->filterResolver = $filterResolver;
        $this->scopeConfig = $scopeConfig;
    }

    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
    public static function convertToFilterCode(string $attributeCode): string
    {
        if (!self::isFilterCode($attributeCode)) {
            return self::ATTR_PREFIX . $attributeCode;
        }
        
        return $attributeCode;
    }
    
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
    public static function convertToAttributeCode(string $filterCode): string
    {
        if (self::isFilterCode($filterCode)) {
            return substr($filterCode, 5);
        }
        
        return $filterCode;
    }

    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
    public static function isFilterCode(string $attributeCode): bool
    {
        return strpos($attributeCode, self::ATTR_PREFIX) === 0;
    }

    /**
     * @param FilterInterface $layerFilter
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function getSettingByLayerFilter(FilterInterface $layerFilter)
    {
        $filterCode = $this->getFilterCode($layerFilter);
        $setting = $this->getFilterSettingByCode($filterCode);
        if ($setting === null) {
            $setting = $this->settingFactory->create(
                ['data' => [FilterSettingInterface::ATTRIBUTE_CODE => $filterCode]]
            );
        }

        $setting->setAttributeModel($layerFilter->getData('attribute_model'));

        return $setting;
    }

    public function getSettingByAttribute(AttributeInterface $attributeModel): ?FilterSettingInterface
    {
        return $this->filterResolver->getFilterSetting($attributeModel);
    }

    /**
     * @param FilterInterface $layerFilter
     * @return null|string
     */
    protected function getFilterCode(FilterInterface $layerFilter)
    {
        $attribute = $layerFilter->getData('attribute_model');
        if (!$attribute) {
            $categorySetting = $layerFilter->getSetting();

            return is_object($categorySetting) ? $categorySetting->getFilterCode() : null;
        }

        return is_object($attribute) ? $attribute->getAttributeCode() : null;
    }

    /**
     * @param string $filterName
     * @param string $configName
     * @return string
     */
    public function getConfig($filterName, $configName)
    {
        return $this->scopeConfig->getValue(
            'amshopby/' . $filterName . '_filter/' . $configName,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getCustomDataForCategoryFilter()
    {
        $data = [];
        foreach ($this->getKeyValueForCategoryFilterConfig() as $key => $value) {
            $data[$key] = $this->scopeConfig->getValue($value, ScopeInterface::SCOPE_WEBSITES);
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getKeyValueForCategoryFilterConfig()
    {
        return [
            'category_tree_depth' => 'amshopby/category_filter/category_tree_depth',
            'subcategories_view' => 'amshopby/category_filter/subcategories_view',
            'subcategories_expand' => 'amshopby/category_filter/subcategories_expand',
            'render_all_categories_tree' => 'amshopby/category_filter/render_all_categories_tree',
            'render_categories_level' => 'amshopby/category_filter/render_categories_level',
        ];
    }

    public function getFilterSettingByCode(?string $code): ?FilterSettingInterface
    {
        return $this->filterResolver->getFilterSettingByCode($code);
    }
}
