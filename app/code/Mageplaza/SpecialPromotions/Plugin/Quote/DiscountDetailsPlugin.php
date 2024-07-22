<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SpecialPromotions
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SpecialPromotions\Plugin\Quote;

use Magento\Quote\Api\Data\TotalSegmentExtensionFactory;
use Magento\Quote\Model\Cart\TotalsConverter;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\ResourceModel\Quote\Address;
use Mageplaza\SpecialPromotions\Api\Data\DiscountDetailsInterfaceFactory;
use Mageplaza\SpecialPromotions\Api\Data\DiscountDetailsItemInterfaceFactory;
use Mageplaza\SpecialPromotions\Helper\Data;

/**
 * Class DiscountDetailsPlugin
 * @package Mageplaza\SpecialPromotions\Plugin\Quote
 */
class DiscountDetailsPlugin
{
    /**
     * @var DiscountDetailsInterfaceFactory
     */
    private $detailsFactory;

    /**
     * @var DiscountDetailsItemInterfaceFactory
     */
    private $itemDetailsFactory;

    /**
     * @var TotalSegmentExtensionFactory
     */
    private $totalSegmentExtensionFactory;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Address
     */
    private $quoteAddress;

    /**
     * @var string
     */
    private $code;

    /**
     * DiscountDetailsPlugin constructor.
     * @param DiscountDetailsInterfaceFactory $detailsFactory
     * @param DiscountDetailsItemInterfaceFactory $itemDetailsFactory
     * @param TotalSegmentExtensionFactory $totalSegmentExtensionFactory
     * @param Session $checkoutSession
     * @param Address $address
     */
    public function __construct(
        DiscountDetailsInterfaceFactory $detailsFactory,
        DiscountDetailsItemInterfaceFactory $itemDetailsFactory,
        TotalSegmentExtensionFactory $totalSegmentExtensionFactory,
        Session $checkoutSession,
        Address $address
    ) {
        $this->detailsFactory = $detailsFactory;
        $this->itemDetailsFactory = $itemDetailsFactory;
        $this->totalSegmentExtensionFactory = $totalSegmentExtensionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteAddress = $address;
        $this->code = TotalsReader::COLLECTOR_TYPE_CODE;
    }

    /**
     * @param TotalsConverter $subject
     * @param array $totalSegments
     * @param array $addressTotals
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterProcess(
        TotalsConverter $subject,
        array $totalSegments,
        array $addressTotals = []
    ) {
        if (!array_key_exists($this->code, $addressTotals)) {
            return $totalSegments;
        }

        $fullInfo = $addressTotals[$this->code]->getFullInfo();
        if (!$fullInfo) {
            return $totalSegments;
        }

        try {
            $quote = $this->checkoutSession->getQuote();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $quote = null;
        }

        if ($quote && $quote->getId()) {
            $addressId = $quote->getShippingAddress()->getId();
            if ($quote->isVirtual()) {
                $addressId = $quote->getBillingAddress()->getId();
            }

            $data = "";

            if ($addressId) {
                $quoteAddress = $this->quoteAddress->getConnection();

                $sql = $quoteAddress->select()->from(
                    $this->quoteAddress->getMainTable(), ['discount_details']
                )->where('address_id = ?', $addressId);

                $data = $quoteAddress->fetchOne($sql);
            }

            if ($data) {
                $fullInfo = Data::jsonDecode($data);
            }
        }

        $finalData = [];
        foreach ($fullInfo as $info) {
            $discountDetails = $this->detailsFactory->create([]);
            $discountDetails->setAmount($info['discount'] ?? 0);
            $discountDetails->setTitle($info['label'] ?? __('Undefine rule'));
            $discountDetails->setRuleId($info['rule_id'] ?? 0);

            $discountItems = [];
            $items = $info['items'] ?? [];
            foreach ($items as $item) {
                if (!isset($item['item_id'])) {
                    continue;
                }
                $discountItemDetails = $this->itemDetailsFactory->create([]);
                $discountItemDetails->setItemId($item['item_id']);
                $discountItemDetails->setAmount($item['discount'] ?? 0);
                $discountItemDetails->setQty((int)($item['qty'] ?? 0));

                $discountItems[] = $discountItemDetails;
            }
            $discountDetails->setItems($discountItems);

            $finalData[] = $discountDetails;
        }

        $attributes = $totalSegments[$this->code]->getExtensionAttributes();
        if ($attributes === null) {
            $attributes = $this->totalSegmentExtensionFactory->create();
        }
        $attributes->setDiscountDetails($finalData);
        $totalSegments[$this->code]->setExtensionAttributes($attributes);

        return $totalSegments;
    }
}
