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

namespace Mageplaza\SpecialPromotions\Plugin\Quote\Model\Quote\Item;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\Repository as QuoteItemRepository;
use Mageplaza\SpecialPromotions\Api\Data\DiscountDetailsInterfaceFactory;
use Mageplaza\SpecialPromotions\Api\Data\DiscountDetailsItemInterfaceFactory;
use Mageplaza\SpecialPromotions\Helper\Data;

/**
 * Class Repository
 * @package Mageplaza\SpecialPromotions\Plugin\Quote\Model\Quote\Item
 */
class Repository
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var DiscountDetailsItemInterfaceFactory
     */
    private $itemDetailsFactory;

    /**
     * @var DiscountDetailsInterfaceFactory
     */
    private $detailsFactory;

    /**
     * Repository constructor.
     * @param Data $helper
     * @param CartRepositoryInterface $quoteRepository
     * @param DiscountDetailsInterfaceFactory $detailsFactory
     * @param DiscountDetailsItemInterfaceFactory $itemDetailsFactory
     */
    public function __construct(
        Data $helper,
        CartRepositoryInterface $quoteRepository,
        DiscountDetailsInterfaceFactory $detailsFactory,
        DiscountDetailsItemInterfaceFactory $itemDetailsFactory
    ) {
        $this->detailsFactory = $detailsFactory;
        $this->helper = $helper;
        $this->quoteRepository = $quoteRepository;
        $this->itemDetailsFactory = $itemDetailsFactory;
    }

    /**
     * @param QuoteItemRepository $subject
     * @param CartItemInterface[] $result
     * @param int $cartId
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function afterGetList(QuoteItemRepository $subject, $result, $cartId)
    {
        if (!$this->helper->isEnabled() || !$this->helper->isDebugEnabled()) {
            return $result;
        }

        /** @var  Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $quote->collectTotals();
        if ($quote->isVirtual()) {
            $addressTotals = $quote->getBillingAddress()->getTotals();
        } else {
            $addressTotals = $quote->getShippingAddress()->getTotals();
        }

        if (isset($addressTotals['discount']) && $addressTotals['discount']) {
            foreach ($result as $item) {
                $extensionAttributes = $item->getExtensionAttributes();
                if (!$extensionAttributes) {
                    continue;
                }
                $fullInfo = $addressTotals['discount']->getFullInfo();
                if (empty($fullInfo)) {
                    continue;
                }

                $finalData = [];
                foreach ($fullInfo as $info) {
                    $discountDetails = $this->detailsFactory->create([]);
                    $discountDetails->setAmount($info['discount'] ?? 0);
                    $discountDetails->setTitle($info['label'] ?? __('Undefine rule'));
                    $discountDetails->setRuleId($info['rule_id'] ?? 0);

                    $discountItems = [];
                    $items = $info['items'] ?? [];

                    foreach ($items as $i) {
                        if (!isset($i['item_id']) || $i['item_id'] !== $item->getItemId()) {
                            continue;
                        }
                        $discountItemDetails = $this->itemDetailsFactory->create([]);
                        $discountItemDetails->setItemId($i['item_id']);
                        $discountItemDetails->setAmount($i['discount'] ?? 0);
                        $discountItemDetails->setQty((int)($i['qty'] ?? 0));

                        $discountItems[] = $discountItemDetails;
                    }
                    $discountDetails->setItems($discountItems);

                    $finalData[] = $discountDetails;
                }
                $extensionAttributes->setDiscountDetails($finalData);
            }
        }

        return $result;
    }
}
