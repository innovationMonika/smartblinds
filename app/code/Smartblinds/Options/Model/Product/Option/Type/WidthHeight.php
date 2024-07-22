<?php declare(strict_types=1);

namespace Smartblinds\Options\Model\Product\Option\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Smartblinds\System\Model\System;
use Smartblinds\System\Model\ResourceModel\System\Collection;
use Smartblinds\System\Model\ResourceModel\System\CollectionFactory;

class WidthHeight extends DefaultType
{
    const GROUP_CODE = 'width_height';
    const TYPE_CODE = 'width_height';

    public function getOptionPrice($optionValue, $basePrice)
    {
        /** @var Product $product */
        $product = $this->getConfigurationItemOption()->getProduct();
        $option = $product->getCustomOption('simple_product');

        /** @var Product $simpleProduct */
        $simpleProduct = $option ? $option->getProduct() : null;
        $simpleProduct = $this->loadProduct($simpleProduct->getId());

        $price = $this->getPrice($optionValue, $basePrice);

        $discount = 1;
        if ($simpleProduct) {
            $finalPrice = $simpleProduct->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getAmount()->getValue();
            $oldPrice = $simpleProduct->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount()->getValue();
            $discount = $finalPrice / $oldPrice;
        }

        $price *= $discount;

        return $price;
    }

    public function getOriginalPrice($optionValue, $basePrice)
    {
        return $this->getPrice($optionValue, $basePrice);
    }

    private function getSystem($systemCategory, $systemType, $controlType, $systemSize, $fabricSize): ?System
    {
        /** @var Collection $systemCollection */
        $systemCollection = ObjectManager::getInstance()->get(CollectionFactory::class)->create();
        $systemCollection->addFieldToFilter('system_category', $systemCategory);
        if ($systemCategory != 'venetian_blinds') {
            $systemCollection->addFieldToFilter('system_type', $systemType);
        }
        if ($controlType) {
            $systemCollection->addFieldToFilter('control_type', $controlType);
        }
        if (!in_array($systemCategory, ['venetian_blinds', 'honeycomb_blinds'])) {
            $systemCollection->addFieldToFilter('system_size', $systemSize);
        }
        if ($systemCategory == 'honeycomb_blinds') {
            $systemCollection->addFieldToFilter('fabric_size', $fabricSize);
        }
        return $systemCollection->getLastItem() ?: null;
    }

    private function loadProduct($productId)
    {
        try {
            /** @var ProductRepositoryInterface $productRepository */
            $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
            return $productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    private function getPrice($optionValue, $basePrice)
    {
        $measurements = is_string($optionValue) ? json_decode($optionValue, true) : $optionValue;
//        $measurements = json_decode($optionValue, true);
        $width = $measurements['width'] ?? null;
        $height = $measurements['height'] ?? null;

        /** @var Product $product */
        $product = $this->getConfigurationItemOption()->getProduct();
        $option = $product->getCustomOption('simple_product');

        /** @var Product $simpleProduct */
        $simpleProduct = $option ? $option->getProduct() : null;
        $simpleProduct = $this->loadProduct($simpleProduct->getId());

        $systemCategory = $simpleProduct ? $simpleProduct->getData('system_category') : null;
        $systemType = $simpleProduct ? $simpleProduct->getData('system_type') : null;
        $controlType = $simpleProduct ? $simpleProduct->getData('control_type') : null;
        $systemSize = $simpleProduct ? $simpleProduct->getData('system_size') : null;
        $fabricSize = $simpleProduct ? $simpleProduct->getData('fabric_size') : null;
        $smartblindsPrice = $simpleProduct ? $simpleProduct->getData('smartblinds_price') : null;
        $system = (($systemType && $systemSize) || in_array($systemCategory, ['venetian_blinds', 'honeycomb_blinds']))
            ? $this->getSystem($systemCategory, $systemType, $controlType, $systemSize,
                $fabricSize) : null;

        if (!$width || !$height || !$system || !$smartblindsPrice) {
            throw new LocalizedException(__('Please fill required attributes'));
        }

        $systemMeterPrice = $system->getStoreMeterPrice();
        $systemBasePrice = $system->getStoreBasePrice();
        $systemPriceCoefficient = $system->getData('price_coefficient');
        $price = ((($width * $height) / 1000000) * $systemPriceCoefficient * $smartblindsPrice)
            + (($width / 1000) * $systemMeterPrice) + $systemBasePrice;

        return $price;
    }
}
