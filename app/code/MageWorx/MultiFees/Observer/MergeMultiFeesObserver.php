<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See https://www.mageworx.com/terms-and-conditions for license details.
 */
declare(strict_types=1);

namespace MageWorx\MultiFees\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

class MergeMultiFeesObserver implements ObserverInterface
{
    const FEE_DETAILS_NAME         = 'mageworx_fee_details';
    const PRODUCT_FEE_DETAILS_NAME = 'mageworx_product_fee_details';

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        $quote  = $observer->getEvent()->getQuote();
        $source = $observer->getEvent()->getSource();

        $this->mergeFees($quote, $source);
        $this->mergeProductFees($quote, $source);
    }

    /**
     * @param Quote $quote
     * @param Quote $source
     * @return void
     */
    protected function mergeFees(Quote $quote, Quote $source): void
    {
        $sourceFeeData = $this->getFeeDataFromAddress($this->getAddressFromQuote($source), self::FEE_DETAILS_NAME);

        if ($sourceFeeData) {
            $quoteAddress = $this->getAddressFromQuote($quote);
            $quoteFeeData = $this->getFeeDataFromAddress($quoteAddress, self::FEE_DETAILS_NAME);

            foreach ($quoteFeeData as $feeId => $datum) {
                if (empty($sourceFeeData[$feeId])) {
                    $sourceFeeData[$feeId] = $datum;
                }
            }

            $quoteAddress->setMageworxFeeDetails($this->serializer->serialize($sourceFeeData));
        }
    }

    /**
     * @param Quote $quote
     * @param Quote $source
     * @return void
     */
    protected function mergeProductFees(Quote $quote, Quote $source): void
    {
        $sourceFeeData = $this->getFeeDataFromAddress(
            $this->getAddressFromQuote($source),
            self::PRODUCT_FEE_DETAILS_NAME
        );

        if ($sourceFeeData) {
            $quoteAddress = $this->getAddressFromQuote($quote);
            $quoteFeeData = $this->getFeeDataFromAddress($quoteAddress, self::PRODUCT_FEE_DETAILS_NAME);
            $linkedIds    = [];

            foreach ($source->getAllVisibleItems() as $sourceItem) {
                foreach ($quote->getAllItems() as $quoteItem) {
                    if ($quoteItem->compare($sourceItem)) {
                        if (!$quoteItem->getId()) {
                            $quoteItem->save();
                        }

                        $linkedIds[$sourceItem->getId()] = $quoteItem->getId();
                        break;
                    }
                }
            }

            $sourceFeeData = $this->prepareSourceFeeData($sourceFeeData, $linkedIds);

            foreach ($quoteFeeData as $feeId => $quoteItemData) {
                if (empty($sourceFeeData[$feeId])) {
                    $sourceFeeData[$feeId] = $quoteItemData;
                } else {
                    foreach ($quoteItemData as $itemId => $fee) {
                        if (empty($sourceFeeData[$feeId][$itemId])) {
                            $sourceFeeData[$feeId][$itemId] = $fee;
                        }
                    }
                }
            }

            $quoteAddress->setMageworxProductFeeDetails($this->serializer->serialize($sourceFeeData));
        }
    }

    /**
     * @param array $sourceFeeData
     * @param array $linkedIds
     * @return array
     */
    protected function prepareSourceFeeData(array $sourceFeeData, array $linkedIds): array
    {
        $data = [];

        foreach ($sourceFeeData as $feeId => $sourceItemData) {
            foreach ($sourceItemData as $itemId => $fee) {
                if (isset($linkedIds[$itemId])) {
                    $newItemId                = $linkedIds[$itemId];
                    $data[$feeId][$newItemId] = $fee;
                }
            }
        }

        return $data;
    }

    /**
     * @param Quote $quote
     * @return Address
     */
    protected function getAddressFromQuote(Quote $quote): Address
    {
        if ($quote->isVirtual()) {
            return $quote->getBillingAddress();
        }

        return $quote->getShippingAddress();
    }

    /**
     * @param Address $address
     * @param string $feeDetailsName
     * @return array
     */
    protected function getFeeDataFromAddress(Address $address, string $feeDetailsName): array
    {
        $feeData = $address->getData($feeDetailsName);
        $feeData = empty($feeData) ? [] : $this->serializer->unserialize($feeData);

        return is_array($feeData) ? $feeData : [];
    }
}
