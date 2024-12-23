<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Block\Checkout;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use MageWorx\MultiFees\Api\QuoteFeeManagerInterface;
use MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection;
use MageWorx\MultiFees\Model\FeeCollectionValidationManager;

abstract class AbstractFeeData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $currency;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \MageWorx\MultiFees\Helper\Data
     */
    protected $helperData;

    /**
     * @var QuoteFeeManagerInterface
     */
    protected $quoteFeeManager;

    /**
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $sessionManager;

    /**
     * @var \MageWorx\MultiFees\Helper\Price
     */
    protected $helperPrice;

    /**
     * @var FeeCollectionValidationManager
     */
    protected $feeCollectionValidationManager;

    /**
     * AbstractFeeData constructor.
     *
     * @param QuoteFeeManagerInterface $quoteFeeManager
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \MageWorx\MultiFees\Helper\Data $helperData
     * @param \MageWorx\MultiFees\Helper\Price $helperPrice
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Customer\Model\Session $customerSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     * @param FeeCollectionValidationManager $feeCollectionValidationManager
     * @param array $data
     */
    public function __construct(
        QuoteFeeManagerInterface $quoteFeeManager,
        \Magento\Framework\View\Element\Template\Context $context,
        \MageWorx\MultiFees\Helper\Data $helperData,
        \MageWorx\MultiFees\Helper\Price $helperPrice,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Customer\Model\Session $customerSession,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Session\SessionManager $sessionManager,
        FeeCollectionValidationManager $feeCollectionValidationManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->quoteFeeManager                = $quoteFeeManager;
        $this->checkoutSession                = $checkoutSession;
        $this->customerSession                = $customerSession;
        $this->currency                       = $currency;
        $this->priceCurrency                  = $priceCurrency;
        $this->helperData                     = $helperData;
        $this->helperPrice                    = $helperPrice;
        $this->sessionManager                 = $sessionManager;
        $this->feeCollectionValidationManager = $feeCollectionValidationManager;
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
        $fee          = $this->checkoutSession->getQuote()->getMageworxFee();
        $feeFormatted = strip_tags($this->priceCurrency->convertAndFormat($fee));
        $quote        = $this->helperData->getQuote();
        $details      = $this->quoteFeeManager->getQuoteDetailsMultifees(
            $quote,
            $this->quoteFeeManager->getAddressFromQuote($quote)->getId()
        );

        $result                     = [];
        $result['is_enable']        = $this->getIsEnable() ? (bool)$fees->count() : false;
        $result['is_display_title'] = ($result['is_enable'] == false) ? false : $this->getIsDisplayTitle();
        $result['fee']              = $feeFormatted;
        $result['url']              = $this->getUrl('multifees/checkout/fee');
        $result['is_valid']         = !isset($details['is_valid']) ? true : $details['is_valid'];
        $result['applyOnClick']     = $this->helperData->isApplyOnClick();

        return $result;
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function serializeJson($data): string
    {
        return $this->helperData->serializeValue($data) ?? '';
    }
    
    /**
     * Get corresponding fee collection
     *
     * @return AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    abstract protected function getMultifees(): AbstractCollection;

    /**
     * @return bool
     */
    protected function getIsEnable(): bool
    {
        return $this->helperData->isEnable();
    }

    /**
     * Check if display title
     * On the cart page we use the external title wrapper.
     *
     * @return boolean
     */
    protected function getIsDisplayTitle(): bool
    {
        $actionList = [];
        if (!empty($this->_data['cart_full_actions']) && is_array($this->_data['cart_full_actions'])) {
            $actionList = $this->_data['cart_full_actions'];
        }
        $actionList[] = 'checkout_cart_index';

        return !in_array($this->_request->getFullActionName(), $actionList);
    }
}
