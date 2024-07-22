<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Block\Catalog\Product;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use MageWorx\MultiFees\Api\FeeCollectionManagerInterface;
use MageWorx\MultiFees\Api\QuoteFeeManagerInterface;
use MageWorx\MultiFees\Api\QuoteProductFeeManagerInterface;
use MageWorx\MultiFees\Model\FeeCollectionValidationManager;
use MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection;

class ProductFeeData extends \MageWorx\MultiFees\Block\Checkout\AbstractFeeData
{
    /**
     * @var \MageWorx\MultiFees\Api\FeeCollectionManagerInterface
     */
    protected $feeCollectionManager;

    /**
     * ProductFeeData constructor.
     *
     * @param QuoteFeeManagerInterface $quoteFeeManager
     * @param QuoteProductFeeManagerInterface $quoteProductFeeManager
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \MageWorx\MultiFees\Helper\Data $helperData
     * @param \MageWorx\MultiFees\Helper\Price $helperPrice
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Customer\Model\Session $customerSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     * @param FeeCollectionValidationManager $feeCollectionValidationManager
     * @param FeeCollectionManagerInterface $feeCollectionManager
     * @param array $data
     */
    public function __construct(
        QuoteFeeManagerInterface $quoteFeeManager,
        QuoteProductFeeManagerInterface $quoteProductFeeManager,
        \Magento\Framework\View\Element\Template\Context $context,
        \MageWorx\MultiFees\Helper\Data $helperData,
        \MageWorx\MultiFees\Helper\Price $helperPrice,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Customer\Model\Session $customerSession,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Session\SessionManager $sessionManager,
        FeeCollectionValidationManager $feeCollectionValidationManager,
        \MageWorx\MultiFees\Api\FeeCollectionManagerInterface $feeCollectionManager,
        array $data = []
    ) {
        parent::__construct(
            $quoteFeeManager,
            $context,
            $helperData,
            $helperPrice,
            $checkoutSession,
            $currency,
            $customerSession,
            $priceCurrency,
            $sessionManager,
            $feeCollectionValidationManager,
            $data
        );

        $this->quoteFeeManager      = $quoteProductFeeManager;
        $this->feeCollectionManager = $feeCollectionManager;
    }

    /**
     * @return \MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getMultifees(): AbstractCollection
    {
        return $this->feeCollectionManager->getProductFeeCollection(
            false,
            false,
            FeeCollectionManagerInterface::HIDDEN_MODE_ONLY
        );
    }

    /**
     * Get specified fee data
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFeeData(): array
    {
        $fees         = $this->getMultifees();
        $fee          = $this->checkoutSession->getQuote()->getMageworxProductFee();
        $feeFormatted = strip_tags($this->priceCurrency->convertAndFormat($fee));
        $quote        = $this->helperData->getQuote();
        $details      = $this->quoteFeeManager->getQuoteDetailsMultifees(
            $quote,
            $this->quoteFeeManager->getAddressFromQuote($quote)->getId()
        );

        $result                     = [];
        $result['is_enable']        = $this->getIsEnable() ? (bool)$fees->count() : false;
        $result['is_display_title'] = ($result['is_enable'] == false) ? false : $this->getIsDisplayTitle();
        $result['title']            = $this->helperData->getProductFeeBlockLabel();
        $result['fee']              = $feeFormatted;
        $result['url']              = $this->getUrl('multifees/checkout/productFee');
        $result['is_valid']         = !isset($details['is_valid']) ? true : $details['is_valid'];
        $result['applyOnClick']     = $this->helperData->isApplyOnClick();

        return $result;
    }

    /**
     * @return bool
     */
    protected function getIsDisplayTitle(): bool
    {
        if ($this->helperData->getProductFeeBlockLabel()) {
            return parent::getIsDisplayTitle();
        }

        return false;
    }
}
