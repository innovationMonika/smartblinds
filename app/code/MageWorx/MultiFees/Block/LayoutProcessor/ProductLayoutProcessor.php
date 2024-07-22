<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Block\LayoutProcessor;

use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Api\FeeCollectionManagerInterface;
use MageWorx\MultiFees\Api\QuoteFeeManagerInterface;
use MageWorx\MultiFees\Api\QuoteProductFeeManagerInterface;
use MageWorx\MultiFees\Helper\Data as Helper;
use MageWorx\MultiFees\Helper\Price as HelperPrice;
use MageWorx\MultiFees\Model\FeeCollectionValidationManager;
use MageWorx\MultiFees\Block\LayoutProcessor;
use MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection;

class ProductLayoutProcessor extends LayoutProcessor
{
    /**
     * @var FeeCollectionManagerInterface
     */
    protected $feeCollectionManager;

    /**
     * ProductLayoutProcessor constructor.
     *
     * @param \Magento\Checkout\Block\Checkout\AttributeMerger $merger
     * @param Helper $helper
     * @param QuoteFeeManagerInterface $quoteFeeManager
     * @param QuoteProductFeeManagerInterface $quoteProductFeeManager
     * @param HelperPrice $helperPrice
     * @param \MageWorx\MultiFees\Block\FeeFormInputPlant $feeFormInputRendererFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Psr\Log\LoggerInterface $logger
     * @param FeeCollectionValidationManager $feeCollectionValidationManager
     * @param FeeCollectionManagerInterface $feeCollectionManager
     */
    public function __construct(
        \Magento\Checkout\Block\Checkout\AttributeMerger $merger,
        Helper $helper,
        QuoteFeeManagerInterface $quoteFeeManager,
        QuoteProductFeeManagerInterface $quoteProductFeeManager,
        HelperPrice $helperPrice,
        \MageWorx\MultiFees\Block\FeeFormInputPlant $feeFormInputRendererFactory,
        \Magento\Framework\Escaper $escaper,
        \Psr\Log\LoggerInterface $logger,
        FeeCollectionValidationManager $feeCollectionValidationManager,
        FeeCollectionManagerInterface $feeCollectionManager
    ) {
        parent::__construct(
            $merger,
            $helper,
            $quoteFeeManager,
            $helperPrice,
            $feeFormInputRendererFactory,
            $escaper,
            $logger,
            $feeCollectionValidationManager
        );
        $this->quoteFeeManager      = $quoteProductFeeManager;
        $this->feeCollectionManager = $feeCollectionManager;
    }

    /**
     * Add our multifees components to the layout if the specific container exists
     *
     * @param array $jsLayout
     * @return array
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function process($jsLayout)
    {
        if ($this->helper->isProductPage()) {
            $this->helper->setCurrentItem($this->helper->getQuoteItemFromCurrentProduct());
        }

        $isApplyOnClick = $this->helper->isApplyOnClick();

        $itemId = '';
        $item   = $this->helper->getCurrentItem();
        if ($item && !$this->helper->isProductPage()) {
            $itemId = $item->getItemId();
            if (isset($jsLayout['components']['mageworx-product-fee-form-container'])) {
                $jsLayout['components']['mageworx-product-fee-form-container' . $itemId]           =
                    $jsLayout['components']['mageworx-product-fee-form-container'];
                $jsLayout['components']['mageworx-product-fee-form-container' . $itemId]['itemId'] = $itemId;
            }
        }

        if (isset($jsLayout['components']['mageworx-product-fee-form-container' . $itemId])) {
            $jsLayout['components']['mageworx-product-fee-form-container' . $itemId]['applyOnClick'] = $isApplyOnClick;

            $jsLayout['components']['mageworx-product-fee-form-container' . $itemId]['typeId']
                = FeeInterface::PRODUCT_TYPE;
        }

        if (isset(
            $jsLayout['components']['mageworx-product-fee-form-container'
            . $itemId]['children']['mageworx-fee-form-fieldset']['children']
        )) {
            $fieldSetPointer = &$jsLayout['components']['mageworx-product-fee-form-container' . $itemId]['children']
            ['mageworx-fee-form-fieldset']['children'];

            try {
                $productFeeComponents = $this->getFeeComponents();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e->getLogMessage());
                $productFeeComponents = [];
            }
            foreach ($productFeeComponents as $component) {
                $fieldSetPointer[] = $component;
            }
            $jsLayout['components']['mageworx-product-fee-form-container' . $itemId]['feeCount'] = count(
                $productFeeComponents
            );
        }

        if (isset(
            $jsLayout['components']['mageworx-product-fee-form-container'
            . $itemId]['children']['mageworx-product-fee-form-fieldset']['children']
        )) {
            $fieldSetPointer = &$jsLayout['components']['mageworx-product-fee-form-container' . $itemId]['children']
            ['mageworx-product-fee-form-fieldset']['children'];

            try {
                $productFeeComponents = $this->getFeeComponents();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e->getLogMessage());
                $productFeeComponents = [];
            }
            foreach ($productFeeComponents as $component) {
                $fieldSetPointer[] = $component;
            }
        }

        return $jsLayout;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFeeComponents(): array
    {
        $currentQuoteItem = $this->helper->getCurrentItem();
        $components       = [];

        if (!$currentQuoteItem instanceof \Magento\Quote\Model\Quote\Item) {
            return $components;
        }

        $feeCollection = $this->getFeeCollection();
        $feeCollection = $this->quoteFeeManager->validateFeeCollectionByQuoteItem($feeCollection, $currentQuoteItem);

        $this->feeCollection = $feeCollection;

        $components = $this->convertFeeCollectionToComponentsArray($feeCollection);
        if (!$this->helper->isProductPage()) {
            $extraComponents = $this->getExtraComponents($feeCollection);

            if (!empty($extraComponents)) {
                $components = $this->mergeAndSortComponents($components, $extraComponents);
            }
        }

        return $components;
    }

    /**
     * @return AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getFeeCollection(): AbstractCollection
    {
        return $this->feeCollectionManager->getProductFeeCollection(false, false, $this->getHiddenMode());
    }

    /**
     * @param AbstractCollection $feeCollection
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function convertFeeCollectionToComponentsArray(AbstractCollection $feeCollection): array
    {
        $quote              = $this->helper->getQuote();
        $details            = $this->quoteFeeManager->getQuoteDetailsMultifees(
            $quote,
            $this->quoteFeeManager->getAddressFromQuote($quote)->getId()
        );
        $currentQuoteItemId = $this->helper->getCurrentQuoteItemId();
        $productDetails     = [];

        if ($currentQuoteItemId) {
            foreach ($details as $feeId => $feeData) {
                foreach ($feeData as $itemId => $data) {
                    if ($itemId != $this->helper->getCurrentQuoteItemId()) {
                        continue;
                    }
                    $productDetails[$feeId] = $data;
                }
            }
        }

        $components = [];
        /** @var \MageWorx\MultiFees\Model\AbstractFee $fee */
        foreach ($feeCollection as $fee) {
            /** @var \MageWorx\MultiFees\Block\FeeFormInput\FeeFormInputRenderInterface $renderer */
            $fee->setCurrentQuoteItemId($this->helper->getCurrentQuoteItemId());
            $renderer     = $this->feeFormInputRendererFactory->create($fee, ['details' => $productDetails]);
            $components[] = $renderer->render();
        }

        return $components;
    }
}
