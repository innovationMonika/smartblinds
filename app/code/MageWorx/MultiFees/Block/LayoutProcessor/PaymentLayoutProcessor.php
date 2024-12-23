<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Block\LayoutProcessor;

use MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection;
use MageWorx\MultiFees\Block\LayoutProcessor;

class PaymentLayoutProcessor extends LayoutProcessor
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
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['beforeMethods']['children']
            ['mageworx-payment-fee-form-container']['children']['mageworx-payment-fee-form-fieldset']['children']
        )
        ) {
            $isApplyOnClick = $this->helper->isApplyOnClick();

            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['beforeMethods']['children']
            ['mageworx-payment-fee-form-container']['applyOnClick'] = $isApplyOnClick;

            $fieldSetPointer = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['beforeMethods']['children']
            ['mageworx-payment-fee-form-container']['children']['mageworx-payment-fee-form-fieldset']['children'];

            try {
                $paymentFeeComponents = $this->getFeeComponents();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e->getLogMessage());
                $paymentFeeComponents = [];
            }
            foreach ($paymentFeeComponents as $component) {
                $fieldSetPointer[] = $component;
            }
        }

        if (isset(
            $jsLayout['components']['mageworx-payment-fee-form-container']['children']
            ['mageworx-payment-fee-form-fieldset']['children']
        )
        ) {
            $isApplyOnClick = $this->helper->isApplyOnClick();

            $jsLayout['components']['mageworx-payment-fee-form-container']['applyOnClick'] = $isApplyOnClick;

            $fieldSetPointer = &$jsLayout['components']['mageworx-payment-fee-form-container']['children']
            ['mageworx-payment-fee-form-fieldset']['children'];

            try {
                $paymentFeeComponents = $this->getFeeComponents();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e->getLogMessage());
                $paymentFeeComponents = [];
            }
            foreach ($paymentFeeComponents as $component) {
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
        return $this->feeCollectionValidationManager->getPaymentFeeCollection($this->getHiddenMode());
    }
}
