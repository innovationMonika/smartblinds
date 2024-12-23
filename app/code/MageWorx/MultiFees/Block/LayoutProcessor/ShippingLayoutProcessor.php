<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Block\LayoutProcessor;

use MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection;
use MageWorx\MultiFees\Block\LayoutProcessor;

class ShippingLayoutProcessor extends LayoutProcessor
{
    /**
     * Add our multifees components to the layout if the specific container exists
     *
     * @param array $jsLayout
     * @return array
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     */
    public function process($jsLayout)
    {
        $jsLayout = $this->addApplyOnClickData($jsLayout);

        if (isset(
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shippingAdditional']['children']
            ['mageworx-shipping-fee-form-container']['children']['mageworx-shipping-fee-form-fieldset']['children']
        )
        ) {
            $isApplyOnClick = $this->helper->isApplyOnClick();

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shippingAdditional']['children']
            ['mageworx-shipping-fee-form-container']['applyOnClick'] = $isApplyOnClick;

            $fieldSetPointer = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shippingAdditional']['children']
            ['mageworx-shipping-fee-form-container']['children']['mageworx-shipping-fee-form-fieldset']['children'];

            try {
                $shippingFeeComponents = $this->getFeeComponents();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e->getLogMessage());
                $shippingFeeComponents = [];
            }
            foreach ($shippingFeeComponents as $component) {
                $fieldSetPointer[] = $component;
            }
        }

        if (isset(
            $jsLayout['components']['mageworx-shipping-fee-form-container']['children']
            ['mageworx-shipping-fee-form-fieldset']['children']
        )
        ) {
            $isApplyOnClick = $this->helper->isApplyOnClick();

            $jsLayout['components']['mageworx-shipping-fee-form-container']['applyOnClick'] = $isApplyOnClick;

            $fieldSetPointer = &$jsLayout['components']['mageworx-shipping-fee-form-container']['children']
            ['mageworx-shipping-fee-form-fieldset']['children'];

            try {
                $shippingFeeComponents = $this->getFeeComponents();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e->getLogMessage());
                $shippingFeeComponents = [];
            }
            foreach ($shippingFeeComponents as $component) {
                $fieldSetPointer[] = $component;
            }
        }

        return $jsLayout;
    }

    /**
     * @return AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getFeeCollection(): AbstractCollection
    {
        return $this->feeCollectionValidationManager->getShippingFeeCollection($this->getHiddenMode());
    }
}
