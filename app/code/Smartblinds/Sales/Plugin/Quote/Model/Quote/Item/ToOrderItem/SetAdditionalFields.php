<?php declare(strict_types=1);

namespace Smartblinds\Sales\Plugin\Quote\Model\Quote\Item\ToOrderItem;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Sales\Api\Data\OrderItemInterface;
use Psr\Log\LoggerInterface;
use Smartblinds\System\Model\ResourceModel\System\Collection;
use Smartblinds\System\Model\ResourceModel\System\CollectionFactory;
use Smartblinds\System\Model\System;

class SetAdditionalFields
{
    protected LoggerInterface $logger;
    private ProductRepositoryInterface $productRepository;
    private CollectionFactory $systemCollectionFactory;
    private ProductResource $productResource;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        CollectionFactory $systemCollectionFactory,
        ProductResource $productResource,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->systemCollectionFactory = $systemCollectionFactory;
        $this->productResource = $productResource;
        $this->logger = $logger;
    }

    public function afterConvert(
        ToOrderItem $subject,
        $orderItem,
        AbstractItem $quoteItem
    ) {
        try {
            if ($orderItem instanceof OrderItemInterface) {
                $product = $quoteItem->getProduct();
                $option = $product->getCustomOption('simple_product');
                /** @var Product $simpleProduct */
                $simpleProduct = $option ? $option->getProduct() : null;
                if ($simpleProduct) {
                    $simpleProduct = $this->loadProduct($simpleProduct->getId());
                    if ($simpleProduct) {
                        $this->setSystemFields($orderItem, $simpleProduct);
                        $orderItem->setData('software', $simpleProduct->getData('smartblinds_sku'));

                    }
                }
                 if ($orderItem->getProductType() === 'simple' && $orderItem->getSku() !== 'curtain_tracks'){
                     $orderItem->setData('software', $orderItem->getSku());
                 }


                $this->setWidthHeight($orderItem, $product);
                $this->setOptionValueCodes($product, $orderItem);
                $orderItem->setData('reference', $this->getOptionValue($product, 'comment'));
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
        } catch (\Error $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
        }

        return $orderItem;
    }

    private function setSystemFields(OrderItemInterface $orderItem, ?Product $product)
    {
        $systemCategory = $product->getData('system_category');
        $systemType = $product->getData('system_type');
        $controlType = $product->getData('control_type');
        $systemSize = $product->getData('system_size');
        $fabricSize = $product->getData('fabric_size');
        $system = $this->getSystem($systemCategory, $systemType, $controlType, $systemSize, $fabricSize);
        if ($system) {
            $orderItem->setData('system_name', $system->getName());
            $orderItem->setData('system_size', $this->getAdminOptionText($product, 'system_size'));
            $orderItem->setData('fabric_size', $this->getAdminOptionText($product, 'fabric_size'));
            $orderItem->setData('system_type', $this->getAdminOptionText($product, 'system_type'));
            $orderItem->setData('control_type', $this->getAdminOptionText($product, 'control_type') ?: "Motor");
            $orderItem->setData('system_color', $this->getAdminOptionText($product, 'system_color'));
            $orderItem->setData('system_category', $this->getAdminOptionText($product, 'system_category'));
        }
    }

    private function getAdminOptionText(Product $product, string $attribute)
    {
        return $this->productResource->getAttribute($attribute)
            ->setStoreId(0)->getSource()->getOptionText($product->getData($attribute));
    }

    private function setWidthHeight(OrderItemInterface $orderItem, Product $product)
    {
        $widthHeightJson = $this->getOptionValue($product, 'width_height');
        if ($widthHeightJson) {
            try {
                $widthHeight = json_decode($widthHeightJson, true);
                $width = $widthHeight['width'];
                $height = $widthHeight['height'];
                $orderItem->setData('width', $width);
                $orderItem->setData('height', $height);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage(), $e->getTrace());
            }
        }
    }

    private function getOptionValue(Product $product, string $optionCode)
    {
        $options = $product->getOptions();
        if (!empty($options)) {
            foreach ($options as $option) {
                $customOption = $product->getCustomOption('option_' . $option->getOptionId());
                if ($option->getOptionCode() == $optionCode && $customOption) {
                    return $customOption->getValue();
                }
            }
        }

        return null;
    }

    private function setOptionValueCodes(Product $product, $orderItem)
    {
        $options = $orderItem->getProductOptions();
        if (isset($options['options']) && !empty($options['options'])) {
            foreach ($options['options'] as $opt) {
                foreach ($product->getOptions() as $option) {
                    $customOption = $product->getCustomOption('option_' . $option->getOptionId());
                    if (!$customOption || ($opt['option_id'] ?? null) != $option->getOptionId()) {
                        continue;
                    }
                    $orderItem->setData($option->getData('option_code'), $opt['value'] ?? null);
                    foreach ($option->getValues() ?: [] as $optionValue) {
                        if ($optionValue->getId() == ($opt['option_value'] ?? null)) {
                            $orderItem->setData($option->getData('option_code'), $optionValue->getData('value_code'));
                        }
                    }
                }
            }
        }

        return null;
    }

    private function getSystem($systemCategory, $systemType, $controlType, $systemSize, $fabricSize): ?System
    {
        /** @var Collection $systemCollection */
        $systemCollection = $this->systemCollectionFactory->create();
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
            return $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());

            return null;
        }
    }
}
