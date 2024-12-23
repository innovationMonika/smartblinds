<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Block;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use MageWorx\MultiFees\Helper\Data as Helper;
use MageWorx\MultiFees\Helper\Price as HelperPrice;
use MageWorx\MultiFees\Model\AbstractFee;
use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Model\FeeCollectionValidationManager;
use MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection;
use MageWorx\MultiFees\Api\FeeCollectionManagerInterface;
use MageWorx\MultiFees\Api\QuoteFeeManagerInterface;

/**
 * Class LayoutProcessor
 */
abstract class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var \Magento\Checkout\Block\Checkout\AttributeMerger
     */
    protected $merger;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $countryCollection;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    protected $regionCollection;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterface
     */
    protected $defaultShippingAddress;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var QuoteFeeManagerInterface
     */
    protected $quoteFeeManager;

    /**
     * @var HelperPrice
     */
    protected $helperPrice;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var FeeFormInputPlant
     */
    protected $feeFormInputRendererFactory;

    /**
     * @var AbstractCollection
     */
    protected $feeCollection;

    /**
     * @var FeeCollectionValidationManager
     */
    protected $feeCollectionValidationManager;

    /**
     * LayoutProcessor constructor.
     *
     * @param \Magento\Checkout\Block\Checkout\AttributeMerger $merger
     * @param Helper $helper
     * @param QuoteFeeManagerInterface $quoteFeeManager
     * @param HelperPrice $helperPrice
     * @param FeeFormInputPlant $feeFormInputRendererFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Psr\Log\LoggerInterface $logger
     * @param FeeCollectionValidationManager $feeCollectionValidationManager
     */
    public function __construct(
        \Magento\Checkout\Block\Checkout\AttributeMerger $merger,
        Helper $helper,
        QuoteFeeManagerInterface $quoteFeeManager,
        HelperPrice $helperPrice,
        \MageWorx\MultiFees\Block\FeeFormInputPlant $feeFormInputRendererFactory,
        \Magento\Framework\Escaper $escaper,
        \Psr\Log\LoggerInterface $logger,
        FeeCollectionValidationManager $feeCollectionValidationManager
    ) {
        $this->merger                         = $merger;
        $this->helper                         = $helper;
        $this->quoteFeeManager                = $quoteFeeManager;
        $this->helperPrice                    = $helperPrice;
        $this->feeFormInputRendererFactory    = $feeFormInputRendererFactory;
        $this->escaper                        = $escaper;
        $this->logger                         = $logger;
        $this->feeCollectionValidationManager = $feeCollectionValidationManager;
    }

    /**
     * Add our multifees components to the layout if the specific container exists
     *
     * @param array $jsLayout
     * @return array
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     */
    abstract public function process($jsLayout);

    /**
     * @param array $jsLayout
     * @return mixed
     */
    protected function addApplyOnClickData(array $jsLayout): array
    {
        $isApplyOnClick = $this->helper->isApplyOnClick();
        if (isset($jsLayout['components']['mageworx-fee-form-container'])) {
            $jsLayout['components']['mageworx-fee-form-container']['applyOnClick'] = $isApplyOnClick;
        }

        if (isset(
            $jsLayout['components']['checkout']['children']['sidebar']['children']
            ['summary']['children']['itemsBefore']['children']['mageworx-fee-form-container']
        )) {
            $jsLayout['components']['checkout']['children']['sidebar']['children']
            ['summary']['children']['itemsBefore']['children']['mageworx-fee-form-container']
            ['applyOnClick'] = $isApplyOnClick;
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
        $this->feeCollection = $this->getFeeCollection();

        return $this->convertFeeCollectionToComponentsArray($this->feeCollection);
    }

    /**
     * @param AbstractCollection $feeCollection
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getExtraComponents(AbstractCollection $feeCollection): array
    {
        $components = [];
        /**
         * @var \MageWorx\MultiFees\Model\AbstractFee $fee
         */
        foreach ($feeCollection as $key => $fee) {
            if ($fee->getEnableDateField() || $fee->getEnableCustomerMessage()) {
                $quote   = $this->helper->getQuote();
                $details = $this->quoteFeeManager->getQuoteDetailsMultifees(
                    $quote,
                    $this->quoteFeeManager->getAddressFromQuote($quote)->getId()
                );

                if ($fee->getEnableDateField()) {
                    $components[] = $this->getFeeDateComponent($fee, $details);
                }
                if ($fee->getEnableCustomerMessage()) {
                    $components[] = $this->getFeeCustomerMessageComponent($fee, $details);
                }
            }
        }

        return $components;
    }

    /**
     * @return AbstractCollection
     */
    abstract protected function getFeeCollection(): AbstractCollection;

    /**
     * Create js-layout components for each fee in the collection
     * Not a fee specific method.
     *
     * @param AbstractCollection $feeCollection
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function convertFeeCollectionToComponentsArray(AbstractCollection $feeCollection): array
    {
        $quote   = $this->helper->getQuote();
        $details = $this->quoteFeeManager->getQuoteDetailsMultifees(
            $quote,
            $this->quoteFeeManager->getAddressFromQuote($quote)->getId()
        );

        $components = [];
        /** @var \MageWorx\MultiFees\Model\AbstractFee $fee */
        foreach ($feeCollection as $fee) {
            /** @var \MageWorx\MultiFees\Block\FeeFormInput\FeeFormInputRenderInterface $renderer */
            $renderer     = $this->feeFormInputRendererFactory->create($fee, ['details' => $details]);
            $components[] = $renderer->render();
        }

        return $components;
    }

    /**
     * @param FeeInterface $fee
     * @param array $details
     * @return array
     */
    protected function getFeeDateComponent(FeeInterface $fee, array $details): array
    {
        $isApplyOnClick = $this->helper->isApplyOnClick();
        if ($fee->getDateFieldTitle()) {
            $label = $fee->getDateFieldTitle();
        } else {
            $label = __('Date for') . ' "' . $fee->getTitle() . '"';
        }

        $scope                     = $this->getScope((int)$fee->getType());
        $component                 = [];
        $component['component']    = 'MageWorx_MultiFees/js/form/element/date';
        $component['config']       = [
            'customScope' => $scope,
            'template'    => 'MageWorx_MultiFees/form/field',
            'elementTmpl' => 'ui/form/element/date'
        ];
        $component['dataScope']    = $scope . '.fee[' . $fee->getId() . '][date]';
        $component['label']        = $label;
        $component['provider']     = 'checkoutProvider';
        $component['visible']      = true;
        $component['validation']   = [];
        $component['applyOnClick'] = $isApplyOnClick;
        $component['feeId']        = $fee->getId();
        $component['sortOrder']    = 2;

        if (!empty($details[$fee->getId()]['date'])) {
            $component['value'] = $this->escaper->escapeHtml($details[$fee->getId()]['date']);
        }

        return $component;
    }

    /**
     * @param \MageWorx\MultiFees\Model\AbstractFee $fee
     * @param array $details
     * @return array
     */
    protected function getFeeCustomerMessageComponent(\MageWorx\MultiFees\Model\AbstractFee $fee, array $details): array
    {
        $isApplyOnClick = $this->helper->isApplyOnClick();
        if ($fee->getCustomerMessageTitle()) {
            $label = $fee->getCustomerMessageTitle();
        } else {
            $label = __('Message for') . ' "' . $fee->getTitle() . '"';
        }

        $scope                  = $this->getScope((int)$fee->getType());
        $component              = [];
        $component['component'] = 'MageWorx_MultiFees/js/form/element/textarea';
        $component['config']    = [
            'customScope' => $scope,
            'template'    => 'MageWorx_MultiFees/form/field',
        ];

        $component['dataScope']    = $scope . '.fee[' . $fee->getId() . '][message]';
        $component['label']        = $label;
        $component['provider']     = 'checkoutProvider';
        $component['visible']      = true;
        $component['validation']   = [];
        $component['applyOnClick'] = $isApplyOnClick;
        $component['feeId']        = $fee->getId();
        $component['sortOrder']    = 1;

        if (!empty($details[$fee->getId()]['message'])) {
            $component['value'] = $this->escaper->escapeHtml($details[$fee->getId()]['message']);
        }

        return $component;
    }

    /**
     * Return scope for fee type
     *
     * @param int type
     * @return string
     */
    protected function getScope(int $type): string
    {
        switch ($type) {
            case AbstractFee::CART_TYPE:
                return 'mageworxFeeForm';
            case AbstractFee::SHIPPING_TYPE:
                return 'mageworxShippingFeeForm';
            case AbstractFee::PAYMENT_TYPE:
                return 'mageworxPaymentFeeForm';
            case AbstractFee::PRODUCT_TYPE:
                return 'mageworxProductFeeForm';
            default:
                return 'mageworxFeeForm';
        }
    }

    /**
     * @return int
     */
    protected function getHiddenMode(): int
    {
        if ($this->helper->isProductPage()) {
            return FeeCollectionManagerInterface::HIDDEN_MODE_ONLY;
        }

        return FeeCollectionManagerInterface::HIDDEN_MODE_EXCLUDE;
    }

    /**
     * @param array $components
     * @param array $extraComponents
     * @return array
     */
    protected function mergeAndSortComponents(array $components, array $extraComponents): array
    {
        usort(
            $components,
            function ($a, $b) {
                return ($a['sortOrder'] - $b['sortOrder']);
            }
        );

        $extraComponents  = $this->groupExtraComponentsByFeeId($extraComponents);
        $mergedComponents = [];

        foreach ($components as $component) {
            if (isset($currentSortOrder)) {
                $component['sortOrder'] = $currentSortOrder;
            } else {
                $currentSortOrder = $component['sortOrder'];
            }

            $mergedComponents[] = $component;
            $feeId              = $component['feeId'];

            if (!empty($extraComponents[$feeId])) {
                foreach ($extraComponents[$feeId] as $extraComponent) {
                    $extraComponent['sortOrder'] = $currentSortOrder + $extraComponent['sortOrder'];
                    $mergedComponents[]          = $extraComponent;
                }
            }

            $currentSortOrder += 5;
        }

        return $mergedComponents;
    }

    /**
     * @param array $components
     * @return array
     */
    protected function groupExtraComponentsByFeeId(array $components): array
    {
        $groupedComponents = [];

        foreach ($components as $component) {
            $feeId = $component['feeId'];

            $groupedComponents[$feeId][] = $component;
        }

        return $groupedComponents;
    }
}
