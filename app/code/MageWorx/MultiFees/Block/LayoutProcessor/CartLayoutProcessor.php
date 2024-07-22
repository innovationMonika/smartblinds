<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Block\LayoutProcessor;

use MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection;
use MageWorx\MultiFees\Block\LayoutProcessor;

/**
 * Class CartLayoutProcessor
 */
class CartLayoutProcessor extends LayoutProcessor
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
            $jsLayout['components']['mageworx-fee-form-container']['children']
            ['mageworx-fee-form-fieldset']['children']
        )
        ) {
            $fieldSetPointer = &$jsLayout['components']['mageworx-fee-form-container']['children']
            ['mageworx-fee-form-fieldset']['children'];

            try {
                $cartFeeComponents = $this->getFeeComponents();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e->getLogMessage());
                $cartFeeComponents = [];
            }
            foreach ($cartFeeComponents as $component) {
                $fieldSetPointer[] = $component;
            }
        }

        if (isset(
            $jsLayout['components']['checkout']['children']['sidebar']['children']
            ['summary']['children']['itemsBefore']['children']['mageworx-fee-form-container']
            ['children']['mageworx-fee-form-fieldset']['children']
        )
        ) {
            $fieldSetPointer = &$jsLayout['components']['checkout']['children']['sidebar']['children']
            ['summary']['children']['itemsBefore']['children']['mageworx-fee-form-container']
            ['children']['mageworx-fee-form-fieldset']['children'];

            try {
                $cartFeeComponents = $this->getFeeComponents();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e->getLogMessage());
                $cartFeeComponents = [];
            }
            foreach ($cartFeeComponents as $component) {
                $fieldSetPointer[] = $component;
            }
        }

        return $jsLayout;
    }

    /**
     * Get components for the available cart fees
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getFeeComponents(): array
    {
        $components      = parent::getFeeComponents();
        $extraComponents = $this->getExtraComponents($this->feeCollection);

        if (empty($extraComponents)) {
            return $components;
        }

        return $this->mergeAndSortComponents($components, $extraComponents);
    }

    /**
     * @return AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getFeeCollection(): AbstractCollection
    {
        return $this->feeCollectionValidationManager->getCartFeeCollection($this->getHiddenMode());
    }
}
