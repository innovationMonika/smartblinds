<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Helper;

use Magento\Tax\Model\Config as TaxConfig;
use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Api\QuoteFeeValidatorInterface;
use MageWorx\MultiFees\Api\QuoteProductFeeValidatorInterface;
use MageWorx\MultiFees\Model\AbstractFee;

/**
 * Config data helper
 */
class Price extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $taxCalculator;

    /**
     * @var \MageWorx\MultiFees\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $helperCart;

    /**
     * @var QuoteFeeValidatorInterface
     */
    protected $quoteFeeValidator;

    /**
     * @var QuoteProductFeeValidatorInterface
     */
    protected $quoteProductFeeValidator;

    /**
     * Price constructor.
     *
     * @param \Magento\Tax\Model\Calculation $taxCalculator
     * @param Data $helperData
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Checkout\Helper\Cart $helperCart
     * @param QuoteFeeValidatorInterface $quoteFeeValidator
     * @param QuoteProductFeeValidatorInterface $quoteProductFeeValidator
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Tax\Model\Calculation $taxCalculator,
        \MageWorx\MultiFees\Helper\Data $helperData,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Checkout\Helper\Cart $helperCart,
        QuoteFeeValidatorInterface $quoteFeeValidator,
        QuoteProductFeeValidatorInterface $quoteProductFeeValidator,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->taxCalculator            = $taxCalculator;
        $this->helperData               = $helperData;
        $this->helperCart               = $helperCart;
        $this->priceCurrency            = $priceCurrency;
        $this->quoteFeeValidator        = $quoteFeeValidator;
        $this->quoteProductFeeValidator = $quoteProductFeeValidator;
        parent::__construct($context);
    }

    /**
     * @param float $price
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $taxClassId
     * @param null|\Magento\Quote\Model\Quote\Address $address
     * @return int
     */
    public function getTaxPrice($price, $quote, $taxClassId, $address = null)
    {
        if (!$quote) {
            return 0;
        }

        if (!$address) {
            $address = $this->getSalesAddress($quote);
        }

        $store             = $quote->getStore();
        $addressTaxRequest = $this->taxCalculator->getRateRequest(
            $address,
            $this->getBillingAddress($quote),
            $quote->getCustomerTaxClassId(),
            $store
        );
        $addressTaxRequest->setProductClassId($taxClassId);
        $rate = $this->taxCalculator->getRate($addressTaxRequest);

        return $this->taxCalculator->calcTaxAmount($price, $rate, false, true);
    }

    /**
     * @param double|int $price
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $taxClassId
     * @param null $address
     * @return mixed
     */
    public function getPriceExcludeTax($price, $quote, $taxClassId, $address = null)
    {
        if (!$quote || !$taxClassId || !$price) {
            return $price;
        }

        if (!$address) {
            $address = $this->getSalesAddress($quote);
        }

        /** @var \Magento\Store\Model\Store $store */
        $store             = $quote->getStore();
        $addressTaxRequest = $this->taxCalculator->getRateRequest(
            $address,
            $quote->getBillingAddress(),
            $quote->getCustomerTaxClassId(),
            $store
        );
        $addressTaxRequest->setProductClassId($taxClassId);
        $rate = $this->taxCalculator->getRate($addressTaxRequest);

        return $this->priceCurrency->round($price / ((100 + $rate) / 100));
    }

    /**
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return mixed
     */
    public function getSalesAddress($quote)
    {
        $address = $quote->getShippingAddress();
        if (!$address->getSubtotal()) {
            $address = $quote->getBillingAddress();
        }

        if ($address->getPostcode() || $address->getCountryId() || $address->getRegionId()) {
            $this->helperData->saveAddressDataToSession($address);
        }
        if (!$address->getPostcode() || !$address->getCountryId() || !$address->getRegionId()) {
            $address = $this->helperData->getAddressDataFromSession($address);
        }

        return $address;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBillingAddress($quote)
    {
        $address = $quote->getBillingAddress();

        if (!$address->getPostcode() || !$address->getCountryId() || !$address->getRegionId()) {
            $address = $this->helperData->getAddressDataFromSession($address);
        }

        return $address;
    }

    /**
     * @param \MageWorx\MultiFees\Model\Option $option
     * @param \MageWorx\MultiFees\Model\AbstractFee $fee
     * @param bool $onlyValue
     * @return float|int|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOptionFormatPrice($option, $fee, $onlyValue = false)
    {
        $price      = $option->getPrice();
        $taxClassId = $fee->getTaxClassId();

        $quote   = $this->helperData->getQuote();
        $address = $this->getSalesAddress($quote);
        if ($fee->getType() == AbstractFee::PRODUCT_TYPE) {
            $validItems = $this->quoteProductFeeValidator->validateItems($quote, $fee);
        } else {
            $validItems = $this->quoteFeeValidator->validateItems($quote, $fee);
        }

        if (!count($validItems)) {
            $price = 0;
        }

        $percent = 0;
        if ($option->getPriceType() == \MageWorx\MultiFees\Model\AbstractFee::PERCENT_ACTION) {
            $percent = $price;

            $appliedTotals       = is_array($fee->getAppliedTotals()) ?
                $fee->getAppliedTotals() :
                explode(',', $fee->getAppliedTotals());
            $baseMageworxFeeLeft = $this->helperData->getBaseFeeLeft(
                $address,
                $appliedTotals,
                $fee,
                $validItems
            );

            $price = ($baseMageworxFeeLeft > 0 && $percent > 0) ? ($baseMageworxFeeLeft * $percent) / 100 : 0;

            if ($fee->getMinAmount() > $price) {
                $price = $fee->getMinAmount();
            }

            if ($onlyValue) {
                return $price;
            }
            $percent = number_format(floatval($percent), 2, null, '') . '%';
        }
        if (!$fee->getIsOnetime()) {
            $price = $this->getQtyMultiplicationPrice($price, $fee, $validItems);
        }

        $store = $quote->getStore();
        $price = $this->priceCurrency->convert($price, $store); // base price - to store price

        if ($onlyValue) {
            return $price;
        }

        // tax_calculation_includes_tax
        if ($this->helperData->isTaxCalculationIncludesTax()) {
            $priceInclTax = $price;
            $price        = $this->getPriceExcludeTax($price, $quote, $fee->getTaxClassId(), $address);
        } else {
            $priceInclTax = $price + $this->getTaxPrice($price, $quote, $taxClassId, $address);
        }

        $taxInBlock = $this->helperData->getTaxInBlock();

        if ($taxInBlock == TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX) {
            $formatPrice = $percent ? $percent : $this->priceCurrency->format($price, false);

            return $formatPrice;
        }

        if ($taxInBlock == TaxConfig::DISPLAY_TYPE_INCLUDING_TAX) {
            $priceInclTax = $this->priceCurrency->format($priceInclTax, false);
            if ($percent) {
                $priceInclTax = $percent . ' (' . $priceInclTax . ')';
            }

            return $priceInclTax;
        }

        if ($taxInBlock == TaxConfig::DISPLAY_TYPE_BOTH) {
            $formatPrice  = $this->priceCurrency->format($price, false);
            $priceInclTax = $this->priceCurrency->format($priceInclTax, false);
            if ($percent) {
                return $percent;
            }

            return $formatPrice . ' (' . __('Incl. Tax %1', $priceInclTax) . ')';
        }
    }

    /**
     * @param int $price
     * @param AbstractFee $fee
     * @param array $validItems
     * @return float|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQtyMultiplicationPrice($price, $fee, $validItems)
    {
        if (0 >= $price) {
            return $price;
        }

        $count = 0;
        switch ($fee->getApplyPer()) {
            case FeeInterface::FEE_APPLY_PER_ITEM:
                if (!empty($validItems)) {
                    foreach ($validItems as $item) {
                        if ($item->getProductType() == 'bundle' && $fee->getUseBundleQty()) {
                            $childCount = 0;
                            foreach ($item->getChildren() as $child) {
                                $childCount += $child->getQty();
                            }
                            $count += $childCount * $item->getQty();
                        } else {
                            $count += $item->getQty();
                        }
                    }
                }
                break;
            case FeeInterface::FEE_APPLY_PER_PRODUCT:
                if (!empty($validItems)) {
                    $count = count($validItems);
                }
                break;
            case FeeInterface::FEE_APPLY_PER_WEIGHT:
                $weight = 0;
                foreach ($validItems as $item) {
                    $weight += ($item->getWeight() * $item->getQty());
                }

                $count = $weight;
                break;
            case FeeInterface::FEE_APPLY_PER_AMOUNT:
                if ($this->helperCart->getCart()->getSubtotal()) {
                    $count = $this->helperCart->getCart()->getSubtotal();
                } else {
                    $count = $this->helperData->getQuote()->getSubtotal();
                }
                break;
        }

        $unitCount = $fee->getUnitCount() > 0 ? $fee->getUnitCount() : 1;

        return $price * intval($count / $unitCount);
    }
}
