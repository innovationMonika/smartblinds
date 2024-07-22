<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Model\Total\Quote;

use Magento\Tax\Model\Config as TaxConfig;
use MageWorx\MultiFees\Api\FeeCollectionManagerInterface;
use MageWorx\MultiFees\Api\QuoteFeeManagerInterface;
use MageWorx\MultiFees\Api\QuoteFeeValidatorInterface;

class Fee extends AbstractFee
{
    /**
     * @var QuoteFeeManagerInterface
     */
    protected $quoteFeeManager;

    /**
     * @var QuoteFeeValidatorInterface
     */
    protected $quoteFeeValidator;

    /**
     * Fee constructor.
     *
     * @param QuoteFeeManagerInterface $quoteFeeManager
     * @param QuoteFeeValidatorInterface $quoteFeeValidator
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \MageWorx\MultiFees\Helper\Data $helperData
     * @param \MageWorx\MultiFees\Helper\Price $helperPrice
     * @param \Magento\Tax\Helper\Data $taxHelperData
     * @param FeeCollectionManagerInterface $feeCollectionManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        QuoteFeeManagerInterface $quoteFeeManager,
        QuoteFeeValidatorInterface $quoteFeeValidator,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \MageWorx\MultiFees\Helper\Data $helperData,
        \MageWorx\MultiFees\Helper\Price $helperPrice,
        \Magento\Tax\Helper\Data $taxHelperData,
        \MageWorx\MultiFees\Api\FeeCollectionManagerInterface $feeCollectionManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->setCode('mageworx_fee');
        $this->quoteFeeManager   = $quoteFeeManager;
        $this->quoteFeeValidator = $quoteFeeValidator;
        parent::__construct(
            $eventManager,
            $storeManager,
            $priceCurrency,
            $helperData,
            $helperPrice,
            $taxHelperData,
            $feeCollectionManager,
            $logger
        );
    }

    /**
     * Collect address fee amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        /** @var \Magento\Quote\Model\Quote\Address $address */
        $address = $shippingAssignment->getShipping()->getAddress();

        if ($this->out($shippingAssignment)) {
            return $this;
        }

        //don't collect fees while currency switched
        if ($quote->getQuoteCurrencyCode() != $this->storeManager->getStore()->getCurrentCurrencyCode()) {
            return $this;
        }

        //@todo check how to avoid using session
        if ($address->getPostcode() || $address->getCountryId() || $address->getRegionId()) {
            $this->helperData->saveAddressDataToSession($address);
        }
        if (!$address->getPostcode() || !$address->getCountryId()) {
            $address = $this->helperData->getAddressDataFromSession($address);
        }

        $feesData = $this->quoteFeeManager->getQuoteDetailsMultifees($quote, $address->getId());
        $feesData = array_filter($feesData, 'is_array');

        // add default fees
        $missedRequiredFees = $this->checkIsRequiredFeesMissed($feesData, $quote);
        if (is_null($feesData) || !empty($missedRequiredFees)) {
            // Adding the fees automatically if there are missed required fees
            // I think here it is necessary to add only the missing fees and not all in a row!
            $feesData = $this->autoAddFeesByParams($quote, $address, $feesData);
        }

        if (empty($feesData)) {
            return $this;
        }

        /** @var \MageWorx\MultiFees\Api\Data\FeeInterface[] $possibleFees */
        $possibleFees = $this->getAllPossibleFees($quote);
        $store        = $quote->getStore();

        $baseMageworxFeeAmount    = 0;
        $baseMageworxFeeTaxAmount = 0;

        foreach ($feesData as $feeId => $data) {
            if (!isset($data['options'])) {
                unset($feesData[$feeId]); // @protection: fee without options
                continue;
            }

            if (empty($possibleFees[$feeId])) {
                unset($feesData[$feeId]); // @protection: remove invalid fees
                continue;
            }

            $appliedTotals       = is_array($data[\MageWorx\MultiFees\Api\Data\FeeInterface::APPLIED_TOTALS]) ?
                $data[\MageWorx\MultiFees\Api\Data\FeeInterface::APPLIED_TOTALS] :
                explode(',', $data[\MageWorx\MultiFees\Api\Data\FeeInterface::APPLIED_TOTALS]);
            $validItems          = $this->quoteFeeValidator->validateItems($quote, $possibleFees[$feeId]);
            $baseMageworxFeeLeft = $this->helperData->getBaseFeeLeft(
                $total,
                $appliedTotals,
                $possibleFees[$feeId],
                $validItems
            );

            $taxClassId = $data['tax_class_id'];

            $feePrice = 0;
            $feeTax   = 0;
            foreach ($data['options'] as $optionId => $value) {
                /**
                 * @see \MageWorx\MultiFees\Helper\Price::getOptionFormatPrice();
                 */

                if (isset($value['percent'])) {
                    $opBasePrice = ($baseMageworxFeeLeft * $value['percent']) / 100;

                    if ($possibleFees[$feeId]->getMinAmount() > $opBasePrice) {
                        $opBasePrice = $possibleFees[$feeId]->getMinAmount();
                    }
                } else {
                    $options = $possibleFees[$feeId]->getOptions();
                    if (isset($options[$optionId])) {
                        // use option price from fee if possible
                        $price = $options[$optionId]->getPrice();
                    } else {
                        $price = $value['base_price'];
                    }
                    $opBasePrice = count($validItems) ? $price : 0;
                }

                if (!$possibleFees[$feeId]->getIsOnetime()) {
                    $opBasePrice = $this->helperPrice->getQtyMultiplicationPrice(
                        $opBasePrice,
                        $possibleFees[$feeId],
                        $validItems
                    );
                }

                $opPrice = $this->priceCurrency->convert($opBasePrice, $store);

                if ($this->helperData->isTaxCalculationIncludesTax()) {
                    $opTax     = $opPrice - $this->helperPrice->getPriceExcludeTax(
                            $opPrice,
                            $quote,
                            $taxClassId,
                            $address
                        );
                    $opBaseTax = $opBasePrice - $this->helperPrice->getPriceExcludeTax(
                            $opBasePrice,
                            $quote,
                            $taxClassId,
                            $address
                        );
                } else {
                    // add tax
                    $opTax     = $this->helperPrice->getTaxPrice($opPrice, $quote, $taxClassId, $address);
                    $opBaseTax = $this->helperPrice->getTaxPrice($opBasePrice, $quote, $taxClassId, $address);

                    $opPrice     += $opTax;
                    $opBasePrice += $opBaseTax;
                }

                // round price
                extract($this->massRound(compact('opPrice', 'opBasePrice', 'opTax', 'opBaseTax')));

                //$opPrice, $opBasePrice = inclTax
                $feesData[$feeId]['options'][$optionId]['price']      = $opPrice;
                $feesData[$feeId]['options'][$optionId]['base_price'] = $opBasePrice;
                $feesData[$feeId]['options'][$optionId]['tax']        = $opTax;
                $feesData[$feeId]['options'][$optionId]['base_tax']   = $opBaseTax;

                $feeTax   += $opBaseTax;
                $feePrice += $opBasePrice;
            }

            $feesData[$feeId]['base_price'] = $feePrice;
            $feesData[$feeId]['price']      = $this->priceCurrency->convert($feePrice, $store);
            $feesData[$feeId]['base_tax']   = $feeTax;
            $feesData[$feeId]['tax']        = $this->priceCurrency->convert($feeTax, $store);

            $baseMageworxFeeAmount    += $feePrice;
            $baseMageworxFeeTaxAmount += $feeTax;
        }

        $mageworxFeeAmount     = $this->priceCurrency->convertAndRound($baseMageworxFeeAmount, $store);
        $baseMageworxFeeAmount = $this->priceCurrency->round($baseMageworxFeeAmount);

        $mageworxFeeTaxAmount     = $this->priceCurrency->convertAndRound($baseMageworxFeeTaxAmount, $store);
        $baseMageworxFeeTaxAmount = $this->priceCurrency->round($baseMageworxFeeTaxAmount);

        $this->addPricesToAddress($total, $address, $mageworxFeeAmount, $mageworxFeeTaxAmount);
        $this->addBasePricesToAddress($total, $address, $baseMageworxFeeAmount, $baseMageworxFeeTaxAmount);
        $this->addFeesDetailsToAddress($total, $address, $feesData);
        $address->save();

        $this->addPricesToQuote($quote, $mageworxFeeAmount, $mageworxFeeTaxAmount);
        $this->addBasePricesToQuote($quote, $baseMageworxFeeAmount, $baseMageworxFeeTaxAmount);
        $this->addFeesDetailsToQuote($quote, $feesData);
        $quote->setAppliedMageworxFeeIds(implode(',', array_keys($feesData)));

        $this->isCollected = true;

        return $this;
    }

    /**
     * Retrieve fees and corresponding options array
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param array $feesPost
     * @return array
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function autoAddFeesByParams(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address $address,
        $feesPost = []
    ): ?array {
        if (empty($feesPost)) {
            $feesPost = $this->quoteFeeManager->getQuoteDetailsMultifees($quote, $address->getId());
            $feesPost = array_filter($feesPost, 'is_array');
        }

        // @uncompleted
        // @TODO add this feature later, it is cool!
        // As I understood it will add the custom message visible for the customer when a fee was applied.
        $feeMessages = null;

        $this->feeCollectionManager
            ->clean()
            ->setQuote($quote)
            ->setAddress($address);

        /** @var \MageWorx\MultiFees\Api\Data\FeeInterface[] $fees */
        $fees = $this->getPossibleFeesItems($quote, true, true);

        if (!count($fees)) {
            return null;
        }

        foreach ($fees as $fee) {
            // Do not add already existent fee
            if (!empty($feesPost[$fee->getId()])) {
                continue;
            }

            // Do not add fee without options
            $feeOptions = $fee->getOptions();
            if (!$feeOptions) {
                continue;
            }

            // Add default data from the model to the fee data-array, needed later during the totals collecting
            $feesPost[$fee->getId()] = $fee->getData();

            // Process options, find default
            /** @var \MageWorx\MultiFees\Model\Option $option */
            foreach ($feeOptions as $option) {
                if (!$option->getIsDefault()) {
                    continue;
                }

                $opValue          = [];
                $opValue['title'] = $option->getTitle();

                if ($option->getPriceType() == 'percent') {
                    $opValue['percent'] = $option->getPrice();
                } else {
                    $opValue['base_price'] = $option->getPrice();
                }
                $feesPost[$fee->getId()]['options'][$option->getId()] = $opValue;

                // @uncompleted
                // @TODO add this feature later, it is cool!
                // As I understood it will add the custom message visible for the customer when a fee was applied.
                if ($feeMessages) {
                    foreach ($feeMessages as $feeMessage) {
                        if ($fee->getId() == $feeMessage->getFeeId()) {
                            $feesPost[$fee->getId()]['message'] = $feeMessage->getMessage();
                        }
                    }
                }
            }
        }

        // Add fees if data is not empty
        if ($feesPost) {
            $this->quoteFeeManager->addFeesToQuote($feesPost, $quote, false, null); //@todo check type
        }

        return $feesPost;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param bool $required
     * @param bool $isDefault
     * @return array|\MageWorx\MultiFees\Api\Data\FeeInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getPossibleFeesItems($quote, $required = false, $isDefault = false)
    {
        // Prevent quote force reloading in case fee total collected for new quote (recursion protection)
        $this->feeCollectionManager->setQuote($quote);
        /** @var \MageWorx\MultiFees\Api\Data\FeeInterface[] $possibleFeesCollection */
        if ($quote->getIsVirtual()) {
            $requiredCartFees       = $this->feeCollectionManager->getCartFeeCollection($required, $isDefault)
                                                                 ->getItems();
            $requiredPaymentFees    = $this->feeCollectionManager->getPaymentFeeCollection($required, $isDefault)
                                                                 ->getItems();
            $possibleFeesCollection = array_merge($requiredCartFees, $requiredPaymentFees);
        } else {
            $requiredCartFees       = $this->feeCollectionManager->getCartFeeCollection($required, $isDefault)
                                                                 ->getItems();
            $requiredShippingFees   = $this->feeCollectionManager->getShippingFeeCollection($required, $isDefault)
                                                                 ->getItems();
            $requiredPaymentFees    = $this->feeCollectionManager->getPaymentFeeCollection($required, $isDefault)
                                                                 ->getItems();
            $possibleFeesCollection = array_merge(
                $requiredCartFees,
                $requiredShippingFees,
                $requiredPaymentFees
            );
        }

        return $possibleFeesCollection;
    }

    /**
     * Check is required fees are missed in the current quote
     *
     * @param array $multiFeesInQuote
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkIsRequiredFeesMissed(array $multiFeesInQuote, \Magento\Quote\Model\Quote $quote)
    {
        /** @var \MageWorx\MultiFees\Api\Data\FeeInterface[] $possibleFeesItems */
        $possibleFeesItems = $this->collectAllRequiredFeesItems($quote);
        /** @var \MageWorx\MultiFees\Api\Data\FeeInterface[] $missedFeeItems */
        $missedFeeItems = [];
        foreach ($possibleFeesItems as $key => $possibleItem) {
            if (empty($multiFeesInQuote[$possibleItem->getId()])) {
                $missedFeeItems[$key] = $possibleItem;
            }
        }

        return !empty($missedFeeItems);
    }

    /**
     * Add fee total information to address
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if ($this->helperData->isEnable()) {
            if (!$total->getMageworxFeeDetails() && !$this->isCollected) {
                $quote->collectTotals();
            }

            if ($total->getMageworxFeeAmount() && $total->getMageworxFeeDetails()) {
                $taxMode = $this->helperData->getTaxInCart();

                if (in_array((int)$taxMode, [TaxConfig::DISPLAY_TYPE_BOTH, TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX])) {

                    $applied = $total->getMageworxFeeDetails();

                    if (is_string($applied)) {
                        $applied = $this->helperData->unserializeValue($applied);
                    }

                    $applied = $this->convertFeeDetailsForTax($applied);

                    return [
                        'code'      => $this->getCode(),
                        'title'     => __('Additional Fees'),
                        'value'     => $total->getMageworxFeeAmount() - $total->getMageworxFeeTaxAmount(),
                        'full_info' => $applied,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param double $mageworxFeeAmount
     * @param double $mageworxFeeTaxAmount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addPricesToAddress($total, $address, $mageworxFeeAmount, $mageworxFeeTaxAmount)
    {
        $total->setMageworxFeeAmount($mageworxFeeAmount);
        $total->setMageworxFeeTaxAmount($mageworxFeeTaxAmount);
        $total->setTotalAmount('mageworx_fee', $mageworxFeeAmount);

        $address->setMageworxFeeAmount($mageworxFeeAmount);
        $address->setMageworxFeeTaxAmount($mageworxFeeTaxAmount);
        $address->setTotalAmount('mageworx_fee', $mageworxFeeAmount);

        $address->setTaxAmount($address->getTaxAmount() + $mageworxFeeTaxAmount);

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param double $baseMageworxFeeAmount
     * @param double $baseMageworxFeeTaxAmount
     * @return $this
     */
    protected function addBasePricesToAddress($total, $address, $baseMageworxFeeAmount, $baseMageworxFeeTaxAmount)
    {
        $total->setBaseMageworxFeeAmount($baseMageworxFeeAmount);
        $total->setBaseMageworxFeeTaxAmount($baseMageworxFeeTaxAmount);
        $total->setBaseTotalAmount('mageworx_fee', $baseMageworxFeeAmount);

        $address->setBaseMageworxFeeAmount($baseMageworxFeeAmount);
        $address->setBaseMageworxFeeTaxAmount($baseMageworxFeeTaxAmount);
        $address->setBaseTotalAmount('mageworx_fee', $baseMageworxFeeAmount);

        $address->setBaseTaxAmount($address->getBaseTaxAmount() + $baseMageworxFeeTaxAmount);

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param array $feesData
     * @return $this
     */
    protected function addFeesDetailsToAddress($total, $address, $feesData)
    {
        $address->setMageworxFeeDetails($this->helperData->serializeValue($feesData));
        $total->setMageworxFeeDetails($this->helperData->serializeValue($feesData));

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return $this
     */
    protected function resetAddress($address)
    {
        $address->setMageworxFeeAmount(0);
        $address->setBaseMageworxFeeAmount(0);
        $address->setMageworxFeeDetails(null);

        return $this;
    }

    /**
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @return bool
     */
    protected function out(\Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment): bool
    {
        if (!$this->helperData->isEnable()) {
            return true;
        }

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return true;
        }

        return false;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $feesData
     * @return $this
     */
    protected function addFeesDetailsToQuote(\Magento\Quote\Model\Quote $quote, array $feesData): Fee
    {
        $quote->setMageworxFeeDetails($this->helperData->serializeValue($feesData));

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param float|int $mageworxFeeAmount
     * @param float|int $mageworxFeeTaxAmount
     * @return $this
     */
    protected function addPricesToQuote(
        \Magento\Quote\Model\Quote $quote,
        float $mageworxFeeAmount = 0,
        float $mageworxFeeTaxAmount = 0
    ) {
        $quote->setMageworxFeeAmount($quote->getMageworxFeeAmount() + $mageworxFeeAmount);
        $quote->setMageworxFeeTaxAmount($quote->getMageworxFeeTaxAmount() + $mageworxFeeTaxAmount);
        $quote->setTaxAmount($quote->getTaxAmount() + $mageworxFeeTaxAmount);

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param float|int $baseMageworxFeeAmount
     * @param float|int $baseMageworxFeeTaxAmount
     * @return $this
     */
    protected function addBasePricesToQuote(
        \Magento\Quote\Model\Quote $quote,
        float $baseMageworxFeeAmount = 0,
        float $baseMageworxFeeTaxAmount = 0
    ) {
        $quote->setBaseMageworxFeeAmount($quote->getBaseMageworxFeeAmount() + $baseMageworxFeeAmount);
        $quote->setBaseMageworxFeeTaxAmount($quote->getBaseMageworxFeeTaxAmount() + $baseMageworxFeeTaxAmount);
        $quote->setBaseTaxAmount($quote->getBaseTaxAmount() + $baseMageworxFeeTaxAmount);

        return $this;
    }
}
